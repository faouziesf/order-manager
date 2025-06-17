@if($templates->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Transporteur</th>
                    <th>Statut</th>
                    <th>Date création</th>
                    <th>Dernière MAJ</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $template)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf text-primary me-2"></i>
                                <div>
                                    <strong>{{ $template->template_name }}</strong>
                                    @if($template->is_default)
                                        <span class="badge bg-warning ms-2">
                                            <i class="fas fa-star me-1"></i>Défaut
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($template->carrier_slug)
                                <span class="badge bg-info">{{ ucfirst($template->carrier_slug) }}</span>
                            @else
                                <span class="text-muted">Universel</span>
                            @endif
                        </td>
                        <td>
                            @if($template->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted">{{ $template->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td>
                            <span class="text-muted">{{ $template->updated_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <!-- Prévisualisation -->
                                <button type="button" class="btn btn-outline-info" 
                                        onclick="previewTemplate({{ $template->id }})" 
                                        title="Prévisualiser">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Édition -->
                                <a href="{{ route('admin.delivery.bl-templates.edit', $template) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Duplication -->
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="duplicateTemplateModal({{ $template->id }}, '{{ addslashes($template->template_name) }}')" 
                                        title="Dupliquer">
                                    <i class="fas fa-copy"></i>
                                </button>
                                
                                <!-- Définir par défaut -->
                                @if(!$template->is_default)
                                    <button type="button" class="btn btn-outline-warning" 
                                            onclick="setAsDefault({{ $template->id }})" 
                                            title="Définir par défaut">
                                        <i class="fas fa-star"></i>
                                    </button>
                                @endif
                                
                                <!-- Suppression -->
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="deleteTemplate({{ $template->id }})" 
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucun template trouvé</h5>
        <p class="text-muted">Créez votre premier template BL pour commencer</p>
        <a href="{{ route('admin.delivery.bl-templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Créer un template
        </a>
    </div>
@endif