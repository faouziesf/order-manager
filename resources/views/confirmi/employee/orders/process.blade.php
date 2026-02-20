@extends('confirmi.layouts.app')
@section('title', 'Traitement #' . ($assignment->order->id ?? 'N/A'))
@section('page-title', 'Poste de traitement')

@section('css')
.process-station { max-width: 1100px; margin: 0 auto; }
.ps-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; flex-wrap:wrap; gap:.75rem; }
.ps-counter { background:var(--royal-blue-900); color:#fff; padding:.4rem 1rem; border-radius:50px; font-size:.78rem; font-weight:700; }
.ps-counter span { color:#fbbf24; }
.order-card { background:#fff; border-radius:16px; border:2px solid var(--border); overflow:hidden; }
.order-card-head { background:linear-gradient(135deg,var(--royal-blue-900),var(--royal-blue-light)); padding:1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between; }
.order-card-head h5 { color:#fff; margin:0; font-weight:800; font-size:1rem; }
.order-id-badge { background:rgba(255,255,255,.15); color:#fff; padding:.25rem .75rem; border-radius:50px; font-size:.72rem; font-weight:700; }
.info-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; padding:1.25rem 1.5rem; }
.info-cell small { display:block; font-size:.7rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-bottom:.2rem; }
.info-cell .val { font-size:.92rem; font-weight:700; color:var(--text-dark); }
.phone-btns { display:flex; gap:.65rem; flex-wrap:wrap; padding:0 1.5rem 1.25rem; }
.btn-phone { display:inline-flex; align-items:center; gap:.5rem; padding:.65rem 1.4rem; background:linear-gradient(135deg,#059669,#10b981); color:#fff; border:none; border-radius:10px; font:.875rem/1 'Inter',sans-serif; font-weight:700; cursor:pointer; text-decoration:none; transition:opacity .2s; }
.btn-phone:hover { opacity:.88; color:#fff; }
.btn-phone-alt { background:linear-gradient(135deg,#0369a1,#0284c7); }
.items-table { padding:0 1.5rem 1.25rem; }
.items-table h6 { font-size:.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:.6rem; }
.action-panel { background:#f8fafc; border-radius:16px; border:2px solid var(--border); overflow:hidden; }
.action-head { background:var(--royal-blue-50); padding:1rem 1.5rem; border-bottom:1px solid var(--border); }
.action-head h6 { margin:0; font-size:.875rem; font-weight:800; color:var(--royal-blue-dark); }
.action-body { padding:1.5rem; }
.result-grid { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; margin-bottom:1rem; }
.result-btn { position:relative; padding:0; }
.result-btn input[type=radio] { position:absolute; opacity:0; width:0; height:0; }
.result-btn label { display:flex; align-items:center; gap:.6rem; padding:.75rem 1rem; border:2px solid var(--border); border-radius:10px; cursor:pointer; transition:all .18s; font-size:.82rem; font-weight:600; color:var(--text-dark); width:100%; }
.result-btn input:checked + label { border-color:currentColor; }
.result-btn.res-confirm input:checked + label { border-color:#10b981; background:#f0fdf4; color:#065f46; }
.result-btn.res-noanswer input:checked + label { border-color:#f59e0b; background:#fffbeb; color:#92400e; }
.result-btn.res-callback input:checked + label { border-color:#3b82f6; background:#eff6ff; color:#1d4ed8; }
.result-btn.res-cancel input:checked + label { border-color:#ef4444; background:#fef2f2; color:#991b1b; }
.result-btn label:hover { background:var(--royal-blue-50); }
.btn-submit-big { width:100%; padding:.875rem; background:linear-gradient(135deg,var(--royal-blue-900),var(--royal-blue-light)); color:#fff; border:none; border-radius:10px; font:700 .95rem/1 'Inter',sans-serif; cursor:pointer; margin-top:.5rem; transition:opacity .2s; }
.btn-submit-big:hover { opacity:.9; }
.track-card { background:#fff; border-radius:12px; border:1.5px solid var(--border); padding:1rem; }
.track-item { display:flex; justify-content:space-between; align-items:center; padding:.35rem 0; border-bottom:1px solid #f1f5f9; font-size:.8rem; }
.track-item:last-child { border-bottom:none; }
.next-btn { display:flex; align-items:center; gap:.5rem; padding:.6rem 1.2rem; background:var(--royal-blue-50); color:var(--royal-blue-dark); border:1.5px solid var(--royal-blue-200); border-radius:10px; font:.8rem/1 'Inter',sans-serif; font-weight:700; text-decoration:none; margin-top:.75rem; transition:background .2s; }
.next-btn:hover { background:var(--royal-blue-100); color:var(--royal-blue-dark); }
.done-badge { text-align:center; padding:1.5rem; }
.done-badge i { font-size:2.5rem; margin-bottom:.5rem; }
@endsection

@section('content')
<div class="process-station">
    <!-- Header bar -->
    <div class="ps-header">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('confirmi.employee.orders.index') }}" class="btn btn-sm btn-outline-royal">
                <i class="fas fa-arrow-left me-1"></i>File d'attente
            </a>
            <h5 class="mb-0 fw-bold" style="font-size:.95rem;">
                Commande <strong>#{{ $assignment->order->id ?? 'N/A' }}</strong>
            </h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="ps-counter"><i class="fas fa-layer-group me-1"></i><span>{{ $remaining }}</span> en attente</span>
            @if($nextAssignment)
            <a href="{{ route('confirmi.employee.orders.process', $nextAssignment) }}" class="btn btn-sm btn-outline-royal">
                Suivante <i class="fas fa-arrow-right ms-1"></i>
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- LEFT: Order info -->
        <div class="col-lg-7">
            <div class="order-card mb-3">
                <div class="order-card-head">
                    <h5><i class="fas fa-shopping-bag me-2"></i>Informations commande</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-id-badge">#{{ $assignment->order->id ?? 'N/A' }}</span>
                        <span class="badge-status badge-{{ $assignment->status }}">
                            {{ match($assignment->status) {
                                'assigned' => 'Assignée', 'in_progress' => 'En cours',
                                'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', default => $assignment->status
                            } }}
                        </span>
                    </div>
                </div>

                @if($assignment->order)
                @php $order = $assignment->order; @endphp

                <div class="info-row">
                    <div class="info-cell">
                        <small>Destinataire</small>
                        <div class="val">{{ $order->customer_name }}</div>
                    </div>
                    <div class="info-cell">
                        <small>Gouvernorat</small>
                        <div class="val">{{ $order->customer_governorate ?? '—' }}</div>
                    </div>
                    <div class="info-cell">
                        <small>Ville</small>
                        <div class="val">{{ $order->customer_city ?? '—' }}</div>
                    </div>
                    <div class="info-cell">
                        <small>Montant total</small>
                        <div class="val" style="color:var(--royal-blue-light);font-size:1.1rem;">
                            {{ number_format($order->total_price, 3) }} DT
                        </div>
                    </div>
                </div>

                @if($order->customer_address)
                <div style="padding:0 1.5rem .75rem;font-size:.82rem;color:var(--text-muted);">
                    <i class="fas fa-map-marker-alt me-1"></i>{{ $order->customer_address }}
                </div>
                @endif

                <!-- Phone call buttons -->
                <div class="phone-btns">
                    <a href="tel:{{ $order->customer_phone }}" class="btn-phone">
                        <i class="fas fa-phone"></i>{{ $order->customer_phone }}
                    </a>
                    @if($order->customer_phone_2)
                    <a href="tel:{{ $order->customer_phone_2 }}" class="btn-phone btn-phone-alt">
                        <i class="fas fa-phone-alt"></i>{{ $order->customer_phone_2 }}
                    </a>
                    @endif
                </div>

                @if($order->notes)
                <div style="padding:0 1.5rem 1rem;background:#fffbeb;border-top:1px solid #fef3c7;">
                    <small class="text-warning fw-bold"><i class="fas fa-sticky-note me-1"></i>Note :</small>
                    <span style="font-size:.83rem;">{{ $order->notes }}</span>
                </div>
                @endif

                @if($order->items && $order->items->count() > 0)
                <div class="items-table">
                    <h6><i class="fas fa-box me-1"></i>Articles</h6>
                    <table class="table table-modern table-sm mb-0">
                        <thead><tr><th>Produit</th><th>Qté</th><th>Prix unit.</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? $item->product_name ?? 'N/A' }}</td>
                                <td><strong>{{ $item->quantity }}</strong></td>
                                <td>{{ number_format($item->unit_price ?? 0, 3) }} DT</td>
                                <td><strong>{{ number_format(($item->unit_price ?? 0) * $item->quantity, 3) }} DT</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                @endif
            </div>

            <!-- Client info -->
            <div class="track-card">
                <div style="font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.6rem;">
                    <i class="fas fa-building me-1"></i>Client Confirmi
                </div>
                <div class="d-flex gap-3 flex-wrap">
                    <div><small class="text-muted d-block">Admin</small><strong style="font-size:.85rem;">{{ $assignment->admin->name ?? '—' }}</strong></div>
                    <div><small class="text-muted d-block">Boutique</small><strong style="font-size:.85rem;">{{ $assignment->admin->shop_name ?? '—' }}</strong></div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Action panel -->
        <div class="col-lg-5">
            <!-- Tracking -->
            <div class="track-card mb-3">
                <div style="font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.65rem;">
                    <i class="fas fa-chart-line me-1"></i>Suivi
                </div>
                <div class="track-item"><span>Tentatives</span><strong>{{ $assignment->attempts }}</strong></div>
                @if($assignment->first_attempt_at)
                <div class="track-item"><span>1ère tentative</span><strong>{{ $assignment->first_attempt_at->format('d/m H:i') }}</strong></div>
                @endif
                @if($assignment->last_attempt_at)
                <div class="track-item"><span>Dernière tentative</span><strong>{{ $assignment->last_attempt_at->format('d/m H:i') }}</strong></div>
                @endif
                @if($assignment->notes)
                <div class="track-item" style="flex-direction:column;align-items:flex-start;gap:.25rem;">
                    <span>Notes précédentes</span><em style="font-size:.78rem;color:var(--text-muted);">{{ $assignment->notes }}</em>
                </div>
                @endif
            </div>

            @if($assignment->canBeManaged())
            <!-- Action -->
            <div class="action-panel">
                <div class="action-head">
                    <h6><i class="fas fa-headset me-2"></i>Enregistrer le résultat de l'appel</h6>
                </div>
                <div class="action-body">
                    @if($assignment->status === 'assigned')
                    <form method="POST" action="{{ route('confirmi.employee.orders.start', $assignment) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-royal w-100">
                            <i class="fas fa-play me-2"></i>Démarrer le traitement
                        </button>
                    </form>
                    @endif

                    <form method="POST" action="{{ route('confirmi.employee.orders.attempt', $assignment) }}" id="attemptForm">
                        @csrf
                        <div class="result-grid">
                            <div class="result-btn res-confirm">
                                <input type="radio" name="result" id="r_confirmed" value="confirmed" required>
                                <label for="r_confirmed"><i class="fas fa-check-circle text-success"></i>Confirmée</label>
                            </div>
                            <div class="result-btn res-noanswer">
                                <input type="radio" name="result" id="r_noanswer" value="no_answer">
                                <label for="r_noanswer"><i class="fas fa-phone-slash" style="color:#f59e0b;"></i>Pas de réponse</label>
                            </div>
                            <div class="result-btn res-callback">
                                <input type="radio" name="result" id="r_callback" value="callback">
                                <label for="r_callback"><i class="fas fa-phone-alt" style="color:#3b82f6;"></i>Rappeler plus tard</label>
                            </div>
                            <div class="result-btn res-cancel">
                                <input type="radio" name="result" id="r_cancelled" value="cancelled">
                                <label for="r_cancelled"><i class="fas fa-times-circle text-danger"></i>Annulée</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Notes (optionnel)</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"
                                      placeholder="Observations sur l'appel...">{{ old('notes', $assignment->notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn-submit-big">
                            <i class="fas fa-save me-2"></i>Enregistrer & Continuer
                        </button>
                    </form>

                    @if($nextAssignment)
                    <a href="{{ route('confirmi.employee.orders.process', $nextAssignment) }}" class="next-btn w-100 justify-content-center mt-2">
                        <i class="fas fa-forward"></i>Passer à la suivante
                    </a>
                    @endif
                </div>
            </div>
            @else
            <!-- Done state -->
            <div class="action-panel">
                <div class="action-head"><h6><i class="fas fa-flag-checkered me-2"></i>Terminée</h6></div>
                <div class="action-body">
                    <div class="done-badge">
                        @if($assignment->status === 'confirmed')
                            <i class="fas fa-check-circle text-success"></i>
                            <p class="fw-bold text-success mb-1">Commande confirmée</p>
                        @elseif($assignment->status === 'cancelled')
                            <i class="fas fa-times-circle text-danger"></i>
                            <p class="fw-bold text-danger mb-1">Commande annulée</p>
                        @else
                            <i class="fas fa-box text-info"></i>
                            <p class="fw-bold text-info mb-1">{{ ucfirst($assignment->status) }}</p>
                        @endif
                        @if($assignment->completed_at)
                        <small class="text-muted">{{ $assignment->completed_at->format('d/m/Y H:i') }}</small>
                        @endif
                    </div>
                    @if($nextAssignment)
                    <a href="{{ route('confirmi.employee.orders.process', $nextAssignment) }}" class="next-btn w-100 justify-content-center">
                        <i class="fas fa-forward"></i>Commande suivante ({{ $remaining - 1 }} restantes)
                    </a>
                    @else
                    <a href="{{ route('confirmi.employee.orders.index') }}" class="next-btn w-100 justify-content-center">
                        <i class="fas fa-check"></i>File terminée !
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
