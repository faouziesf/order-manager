<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PickupAddressController extends Controller
{
    /**
     * Afficher la liste des adresses d'enlèvement
     */
    public function index(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $addresses = $admin->pickupAddresses()
                ->orderBy('is_default', 'desc')
                ->orderBy('name')
                ->paginate(20);

            if ($request->ajax()) {
                return response()->json([
                    'addresses' => $addresses,
                    'html' => view('admin.delivery.pickup-addresses.table', compact('addresses'))->render()
                ]);
            }

            return view('admin.delivery.pickup-addresses.index', compact('addresses'));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans PickupAddressController@index: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Erreur lors du chargement des adresses'], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des adresses');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.delivery.pickup-addresses.create');
    }

    /**
     * Créer une nouvelle adresse d'enlèvement
     */
    public function store(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            // Vérifier la limite d'adresses
            if ($admin->pickupAddresses()->count() >= 10) {
                throw new \Exception('Vous avez atteint la limite de 10 adresses d\'enlèvement.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:pickup_addresses,name,NULL,id,admin_id,' . $admin->id,
                'contact_name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'postal_code' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'is_default' => 'boolean',
            ], [
                'name.required' => 'Le nom de l\'adresse est obligatoire',
                'name.unique' => 'Une adresse avec ce nom existe déjà',
                'contact_name.required' => 'Le nom du contact est obligatoire',
                'address.required' => 'L\'adresse est obligatoire',
                'phone.required' => 'Le numéro de téléphone est obligatoire',
                'email.email' => 'Le format de l\'email est invalide',
            ]);

            DB::beginTransaction();

            // Si c'est la première adresse ou marquée comme par défaut
            $isFirstAddress = !$admin->pickupAddresses()->exists();
            $isDefault = $isFirstAddress || ($validated['is_default'] ?? false);

            $address = PickupAddress::create([
                'admin_id' => $admin->id,
                'name' => $validated['name'],
                'contact_name' => $validated['contact_name'],
                'address' => $validated['address'],
                'postal_code' => $validated['postal_code'],
                'city' => $validated['city'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'is_default' => $isDefault,
                'is_active' => true,
            ]);

            // Si marquée comme par défaut, désactiver les autres
            if ($isDefault) {
                PickupAddress::where('admin_id', $admin->id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Adresse d\'enlèvement créée avec succès',
                    'address' => $address
                ]);
            }

            return redirect()->route('admin.delivery.pickup-addresses.index')
                ->with('success', 'Adresse d\'enlèvement créée avec succès');

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
            Log::error('Erreur dans PickupAddressController@store: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(PickupAddress $pickupAddress)
    {
        try {
            $this->authorize('update', $pickupAddress);
            
            return view('admin.delivery.pickup-addresses.edit', compact('pickupAddress'));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans PickupAddressController@edit: ' . $e->getMessage());
            return redirect()->route('admin.delivery.pickup-addresses.index')
                ->with('error', 'Erreur lors du chargement de l\'adresse');
        }
    }

    /**
     * Mettre à jour une adresse d'enlèvement
     */
    public function update(Request $request, PickupAddress $pickupAddress)
    {
        try {
            $this->authorize('update', $pickupAddress);
            $admin = Auth::guard('admin')->user();

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:pickup_addresses,name,' . $pickupAddress->id . ',id,admin_id,' . $admin->id,
                'contact_name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'postal_code' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'is_default' => 'boolean',
            ]);

            DB::beginTransaction();

            $pickupAddress->update([
                'name' => $validated['name'],
                'contact_name' => $validated['contact_name'],
                'address' => $validated['address'],
                'postal_code' => $validated['postal_code'],
                'city' => $validated['city'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
            ]);

            // Gérer le changement de statut par défaut
            if ($validated['is_default'] ?? false) {
                $pickupAddress->setAsDefault();
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Adresse mise à jour avec succès',
                    'address' => $pickupAddress->fresh()
                ]);
            }

            return redirect()->route('admin.delivery.pickup-addresses.index')
                ->with('success', 'Adresse mise à jour avec succès');

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
            Log::error('Erreur dans PickupAddressController@update: ' . $e->getMessage());
            
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
     * Supprimer une adresse d'enlèvement
     */
    public function destroy(PickupAddress $pickupAddress)
    {
        try {
            $this->authorize('delete', $pickupAddress);

            if (!$pickupAddress->canBeDeleted()) {
                throw new \Exception('Cette adresse ne peut pas être supprimée car elle est utilisée par des enlèvements.');
            }

            $addressName = $pickupAddress->name;
            $pickupAddress->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Adresse \"{$addressName}\" supprimée avec succès"
                ]);
            }

            return redirect()->route('admin.delivery.pickup-addresses.index')
                ->with('success', "Adresse \"{$addressName}\" supprimée avec succès");

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupAddressController@destroy: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Activer/désactiver une adresse
     */
    public function toggleStatus(PickupAddress $pickupAddress)
    {
        try {
            $this->authorize('toggleStatus', $pickupAddress);

            if ($pickupAddress->is_active && !$pickupAddress->canBeDeactivated()) {
                throw new \Exception('Cette adresse ne peut pas être désactivée car c\'est la seule adresse active.');
            }

            $pickupAddress->update(['is_active' => !$pickupAddress->is_active]);
            
            $status = $pickupAddress->is_active ? 'activée' : 'désactivée';
            $message = "Adresse \"{$pickupAddress->name}\" {$status} avec succès";

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'is_active' => $pickupAddress->is_active
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupAddressController@toggleStatus: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Définir comme adresse par défaut
     */
    public function setDefault(PickupAddress $pickupAddress)
    {
        try {
            $this->authorize('setAsDefault', $pickupAddress);

            if (!$pickupAddress->is_active) {
                throw new \Exception('Seules les adresses actives peuvent être définies par défaut.');
            }

            $pickupAddress->setAsDefault();
            
            $message = "Adresse \"{$pickupAddress->name}\" définie comme par défaut";

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupAddressController@setDefault: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}