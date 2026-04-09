@props([
    'position' => 'top-right',
    'autoDismiss' => 4000,
])

@php
    $positionStyles = [
        'top-right'    => 'top:1rem;right:1rem;',
        'top-center'   => 'top:1rem;left:50%;transform:translateX(-50%);',
        'bottom-right' => 'bottom:1rem;right:1rem;',
    ];
    $posStyle = $positionStyles[$position] ?? $positionStyles['top-right'];
@endphp

{{-- Toast container --}}
<div id="toast-container" style="position:fixed;{{ $posStyle }}z-index:9999;display:flex;flex-direction:column;gap:.5rem;pointer-events:none;"></div>

{{-- Auto-show session flash messages --}}
@if(session('success') || session('error') || session('warning') || session('info'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
    showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
    showToast('error', @json(session('error')));
    @endif
    @if(session('warning'))
    showToast('warning', @json(session('warning')));
    @endif
    @if(session('info'))
    showToast('info', @json(session('info')));
    @endif
});
</script>
@endif

<script>
function showToast(type, message) {
    var container = document.getElementById('toast-container');
    if (!container) return;

    var colors = {
        success: { bg: '#f0fdf4', border: '#10b981', icon: 'fa-check-circle', color: '#065f46' },
        error:   { bg: '#fef2f2', border: '#ef4444', icon: 'fa-exclamation-circle', color: '#991b1b' },
        warning: { bg: '#fffbeb', border: '#f59e0b', icon: 'fa-exclamation-triangle', color: '#92400e' },
        info:    { bg: '#eff6ff', border: '#3b82f6', icon: 'fa-info-circle', color: '#1e40af' }
    };
    var c = colors[type] || colors.info;

    var toast = document.createElement('div');
    toast.style.cssText = 'pointer-events:auto;display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border-radius:10px;border-left:4px solid ' + c.border + ';background:' + c.bg + ';color:' + c.color + ';font-size:.875rem;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.1);min-width:280px;max-width:420px;opacity:0;transform:translateX(20px);transition:opacity .3s,transform .3s;';
    toast.innerHTML = '<i class="fas ' + c.icon + '" style="font-size:1rem;flex-shrink:0;"></i><span style="flex:1;">' + message + '</span><button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:' + c.color + ';opacity:.5;padding:0;font-size:.9rem;"><i class="fas fa-times"></i></button>';

    container.appendChild(toast);
    requestAnimationFrame(function() {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    });

    @if($autoDismiss > 0)
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(function() { toast.remove(); }, 300);
    }, {{ $autoDismiss }});
    @endif
}
</script>
