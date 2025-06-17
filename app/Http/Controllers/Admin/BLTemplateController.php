<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BLTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BLTemplateController extends Controller
{
    /**
     * Afficher la liste des templates BL
     */
    public function index(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $query = $admin->blTemplates();

            // Filtres
            if ($request->filled('carrier')) {
                $query->where('carrier_slug', $request->carrier);
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'default') {
                    $query->where('is_default', true);
                }
            }

            $templates = $query->orderBy('is_default', 'desc')
                ->orderBy('carrier_slug')
                ->orderBy('template_name')
                ->paginate(20);

            // Statistiques
            $stats = [
                'total' => $admin->blTemplates()->count(),
                'active' => $admin->blTemplates()->where('is_active', true)->count(),
                'default' => $admin->blTemplates()->where('is_default', true)->count(),
                'by_carrier' => $admin->blTemplates()
                    ->selectRaw('carrier_slug, COUNT(*) as count')
                    ->groupBy('carrier_slug')
                    ->pluck('count', 'carrier_slug')
                    ->toArray(),
            ];

            if ($request->ajax()) {
                return response()->json([
                    'templates' => $templates,
                    'stats' => $stats,
                    'html' => view('admin.delivery.bl-templates.table', compact('templates'))->render()
                ]);
            }

            return view('admin.delivery.bl-templates.index', compact('templates', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur dans BLTemplateController@index: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Erreur lors du chargement des templates'], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des templates');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        // Récupérer les transporteurs disponibles
        $carriers = $admin->deliveryConfigurations()
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(function($config) {
                return [$config->carrier_slug => $config->carrier_display_name];
            })
            ->unique()
            ->toArray();

        $defaultConfig = BLTemplate::getDefaultLayoutConfig();
        $availableFields = BLTemplate::getAvailableFields();
        $fieldTypes = BLTemplate::getFieldTypes();
        $barcodeFormats = BLTemplate::getBarcodeFormats();

        return view('admin.delivery.bl-templates.create', compact(
            'carriers', 
            'defaultConfig', 
            'availableFields', 
            'fieldTypes', 
            'barcodeFormats'
        ));
    }

    /**
     * Créer un nouveau template BL
     */
    public function store(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();

            $validated = $request->validate([
                'template_name' => 'required|string|max:255',
                'carrier_slug' => 'nullable|string|max:50',
                'layout_config' => 'required|array',
                'is_default' => 'boolean',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'template_name.required' => 'Le nom du template est obligatoire',
                'layout_config.required' => 'La configuration du layout est obligatoire',
                'logo.image' => 'Le logo doit être une image',
                'logo.mimes' => 'Le logo doit être au format: jpeg, png, jpg, gif, svg',
                'logo.max' => 'Le logo ne doit pas dépasser 2MB',
            ]);

            // Vérifier l'unicité du nom pour cet admin et transporteur
            $existing = BLTemplate::where('admin_id', $admin->id)
                ->where('carrier_slug', $validated['carrier_slug'])
                ->where('template_name', $validated['template_name'])
                ->exists();
                
            if ($existing) {
                throw new ValidationException(['template_name' => ['Un template avec ce nom existe déjà pour ce transporteur.']]);
            }

            DB::beginTransaction();

            // Traiter l'upload du logo
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('bl-templates/logos', 'public');
                
                // Mettre à jour la config du layout avec le chemin du logo
                $validated['layout_config']['logo']['path'] = $logoPath;
                $validated['layout_config']['logo']['enabled'] = true;
            }

            $template = BLTemplate::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $validated['carrier_slug'],
                'template_name' => $validated['template_name'],
                'layout_config' => $validated['layout_config'],
                'is_default' => $validated['is_default'] ?? false,
                'is_active' => true,
            ]);

            // Si marqué comme par défaut, désactiver les autres
            if ($template->is_default) {
                $template->setAsDefault();
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Template BL créé avec succès',
                    'template' => $template
                ]);
            }

            return redirect()->route('admin.delivery.bl-templates.index')
                ->with('success', 'Template BL créé avec succès');

        } catch (ValidationException $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans BLTemplateController@store: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du template'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la création du template')->withInput();
        }
    }

    /**
     * Afficher un template BL
     */
    public function show(BLTemplate $blTemplate)
    {
        try {
            $this->authorize('view', $blTemplate);
            
            $validation = $blTemplate->validateConfig();
            
            return view('admin.delivery.bl-templates.show', compact('blTemplate', 'validation'));

        } catch (\Exception $e) {
            Log::error('Erreur dans BLTemplateController@show: ' . $e->getMessage());
            return redirect()->route('admin.delivery.bl-templates.index')
                ->with('error', 'Erreur lors du chargement du template');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(BLTemplate $blTemplate)
    {
        try {
            $this->authorize('update', $blTemplate);
            
            $admin = Auth::guard('admin')->user();
            
            // Récupérer les transporteurs disponibles
            $carriers = $admin->deliveryConfigurations()
                ->where('is_active', true)
                ->get()
                ->mapWithKeys(function($config) {
                    return [$config->carrier_slug => $config->carrier_display_name];
                })
                ->unique()
                ->toArray();

            $availableFields = BLTemplate::getAvailableFields();
            $fieldTypes = BLTemplate::getFieldTypes();
            $barcodeFormats = BLTemplate::getBarcodeFormats();

            return view('admin.delivery.bl-templates.edit', compact(
                'blTemplate',
                'carriers', 
                'availableFields', 
                'fieldTypes', 
                'barcodeFormats'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur dans BLTemplateController@edit: ' . $e->getMessage());
            return redirect()->route('admin.delivery.bl-templates.index')
                ->with('error', 'Erreur lors du chargement du template');
        }
    }

    /**
     * Mettre à jour un template BL
     */
    public function update(Request $request, BLTemplate $blTemplate)
    {
        try {
            $this->authorize('update', $blTemplate);
            $admin = Auth::guard('admin')->user();

            $validated = $request->validate([
                'template_name' => 'required|string|max:255',
                'carrier_slug' => 'nullable|string|max:50',
                'layout_config' => 'required|array',
                'is_default' => 'boolean',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'remove_logo' => 'boolean',
            ]);

            // Vérifier l'unicité du nom
            $existing = BLTemplate::where('admin_id', $admin->id)
                ->where('carrier_slug', $validated['carrier_slug'])
                ->where('template_name', $validated['template_name'])
                ->where('id', '!=', $blTemplate->id)
                ->exists();
                
            if ($existing) {
                throw new ValidationException(['template_name' => ['Un template avec ce nom existe déjà pour ce transporteur.']]);
            }

            DB::beginTransaction();

            // Traiter le logo
            $layoutConfig = $validated['layout_config'];
            
            if ($request->boolean('remove_logo')) {
                // Supprimer l'ancien logo
                if (!empty($blTemplate->layout_config['logo']['path'])) {
                    Storage::disk('public')->delete($blTemplate->layout_config['logo']['path']);
                }
                $layoutConfig['logo']['enabled'] = false;
                $layoutConfig['logo']['path'] = null;
            }
            
            if ($request->hasFile('logo')) {
                // Supprimer l'ancien logo
                if (!empty($blTemplate->layout_config['logo']['path'])) {
                    Storage::disk('public')->delete($blTemplate->layout_config['logo']['path']);
                }
                
                // Sauvegarder le nouveau logo
                $logoPath = $request->file('logo')->store('bl-templates/logos', 'public');
                $layoutConfig['logo']['path'] = $logoPath;
                $layoutConfig['logo']['enabled'] = true;
            }

            $blTemplate->update([
                'template_name' => $validated['template_name'],
                'carrier_slug' => $validated['carrier_slug'],
                'layout_config' => $layoutConfig,
            ]);

            // Gérer le statut par défaut
            if ($validated['is_default'] ?? false) {
                $blTemplate->setAsDefault();
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Template BL mis à jour avec succès',
                    'template' => $blTemplate->fresh()
                ]);
            }

            return redirect()->route('admin.delivery.bl-templates.index')
                ->with('success', 'Template BL mis à jour avec succès');

        } catch (ValidationException $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans BLTemplateController@update: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour')->withInput();
        }
    }

    /**
     * Supprimer un template BL
     */
    public function destroy(BLTemplate $blTemplate)
    {
        try {
            $this->authorize('delete', $blTemplate);

            // Supprimer le logo associé
            if (!empty($blTemplate->layout_config['logo']['path'])) {
                Storage::disk('public')->delete($blTemplate->layout_config['logo']['path']);
            }

            $templateName = $blTemplate->template_name;
            $blTemplate->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Template \"{$templateName}\" supprimé avec succès"
                ]);
            }

            return redirect()->route('admin.delivery.bl-templates.index')
                ->with('success', "Template \"{$templateName}\" supprimé avec succès");

        } catch (\Exception $e) {
            Log::error('Erreur dans BLTemplateController@destroy: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la suppression');
        }
    }

    /**
     * Dupliquer un template
     */
    public function duplicate(Request $request, BLTemplate $blTemplate)
    {
        try {
            $this->authorize('view', $blTemplate);

            $validated = $request->validate([
                'new_name' => 'required|string|max:255'
            ]);

            $duplicate = $blTemplate->duplicate($validated['new_name']);

            return response()->json([
                'success' => true,
                'message' => 'Template dupliqué avec succès',
                'template_id' => $duplicate->id,
                'redirect_url' => route('admin.delivery.bl-templates.edit', $duplicate)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erreur dans BLTemplateController@duplicate: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la duplication'
            ], 500);
        }
    }

    /**
     * Définir comme template par défaut
     */
    public function setDefault(BLTemplate $blTemplate)
    {
        try {
            $this->authorize('update', $blTemplate);

            $blTemplate->setAsDefault();

            $message = "Template \"{$blTemplate->template_name}\" défini comme par défaut";

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur dans BLTemplateController@setDefault: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    /**
     * Prévisualiser un template
     */
    public function preview(Request $request, BLTemplate $blTemplate)
    {
        try {
            $this->authorize('view', $blTemplate);

            // Créer des données de test pour la prévisualisation
            $sampleData = [
                'customer' => [
                    'name' => 'Ahmed Ben Ali',
                    'phone' => '+216 98 123 456',
                    'address' => '123 Avenue Habib Bourguiba, Tunis 1001',
                    'city' => 'Tunis',
                    'email' => 'ahmed@example.com',
                ],
                'sender' => [
                    'name' => 'Mon Entreprise',
                    'address' => '456 Rue de la Liberté, Ariana 2080',
                    'phone' => '+216 71 123 456',
                    'email' => 'contact@monentreprise.tn',
                ],
                'shipment' => [
                    'tracking_number' => 'TN' . date('Ymd') . '001',
                    'order_number' => 'CMD-' . date('Ymd') . '-001',
                    'return_barcode' => 'RET_' . date('Ymd') . '_001',
                    'weight' => '1.5',
                    'pieces_count' => '1',
                    'content_description' => 'Produits divers',
                ],
                'financial' => [
                    'total_amount' => '89.500',
                    'cod_amount' => '89.500',
                    'shipping_cost' => '7.000',
                ],
                'dates' => [
                    'shipping_date' => now()->format('d/m/Y'),
                    'pickup_date' => now()->format('d/m/Y'),
                    'estimated_delivery' => now()->addDays(2)->format('d/m/Y'),
                ],
            ];

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'sample_data' => $sampleData,
                    'config' => $blTemplate->layout_config
                ]);
            }

            return view('admin.delivery.bl-templates.preview', compact('blTemplate', 'sampleData'));

        } catch (\Exception $e) {
            Log::error('Erreur dans BLTemplateController@preview: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération de la prévisualisation'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la génération de la prévisualisation');
        }
    }
}