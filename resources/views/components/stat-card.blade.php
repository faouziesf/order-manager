@props([
    'value',
    'label',
    'icon' => 'fas fa-chart-bar',
    'color' => 'blue',
    'href' => null,
    'footer' => null,
])

@php
    $colorMap = [
        'blue'   => ['bg' => '#eff6ff', 'icon' => '#3b82f6', 'value' => '#1e40af'],
        'green'  => ['bg' => '#f0fdf4', 'icon' => '#10b981', 'value' => '#065f46'],
        'orange' => ['bg' => '#fff7ed', 'icon' => '#f97316', 'value' => '#9a3412'],
        'red'    => ['bg' => '#fef2f2', 'icon' => '#ef4444', 'value' => '#991b1b'],
        'purple' => ['bg' => '#faf5ff', 'icon' => '#8b5cf6', 'value' => '#5b21b6'],
        'cyan'   => ['bg' => '#ecfeff', 'icon' => '#06b6d4', 'value' => '#155e75'],
        'yellow' => ['bg' => '#fefce8', 'icon' => '#eab308', 'value' => '#854d0e'],
    ];
    $c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif
    style="display:block;background:{{ $c['bg'] }};border-radius:12px;padding:1.25rem;text-decoration:none;color:inherit;transition:box-shadow .2s,transform .15s;"
    onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.08)';this.style.transform='translateY(-1px)'"
    onmouseout="this.style.boxShadow='none';this.style.transform='none'"
    {{ $attributes }}>
    <div style="display:flex;align-items:center;gap:1rem;">
        <div style="width:48px;height:48px;background:{{ $c['icon'] }}15;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="{{ $icon }}" style="font-size:1.2rem;color:{{ $c['icon'] }};"></i>
        </div>
        <div style="min-width:0;flex:1;">
            <div style="font-size:1.75rem;font-weight:800;line-height:1.1;color:{{ $c['value'] }};">{{ $value }}</div>
            <div style="font-size:.82rem;color:#64748b;margin-top:.15rem;">{{ $label }}</div>
        </div>
    </div>
    @if($footer)
    <div style="margin-top:.75rem;padding-top:.6rem;border-top:1px solid rgba(0,0,0,.06);font-size:.78rem;color:{{ $c['icon'] }};display:flex;align-items:center;gap:.35rem;">
        <i class="fas fa-arrow-right" style="font-size:.65rem;"></i>
        <span>{{ $footer }}</span>
    </div>
    @endif
</{{ $tag }}>
