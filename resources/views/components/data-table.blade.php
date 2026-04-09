@props([
    'title',
    'icon' => null,
    'badgeCount' => null,
    'headers' => [],
    'emptyIcon' => 'fas fa-inbox',
    'emptyTitle' => 'Aucune donnée',
    'emptyText' => '',
    'striped' => true,
])

<div style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden;" {{ $attributes }}>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
        <h6 style="margin:0;font-size:.95rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:.5rem;">
            @if($icon)
            <i class="{{ $icon }}" style="color:#3b82f6;"></i>
            @endif
            {{ $title }}
        </h6>
        @if(!is_null($badgeCount))
        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:24px;height:24px;padding:0 .5rem;border-radius:20px;font-size:.75rem;font-weight:700;color:#fff;background:#3b82f6;">{{ $badgeCount }}</span>
        @endif
    </div>

    {{-- Table --}}
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
            @if(count($headers))
            <thead>
                <tr>
                    @foreach($headers as $header)
                    <th style="padding:.65rem 1rem;text-align:left;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em;color:#64748b;background:#f8fafc;border-bottom:2px solid #e2e8f0;white-space:nowrap;">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            @endif
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    {{-- Empty state (shown via CSS when tbody is empty) --}}
    @if(isset($empty) || $emptyTitle)
    <style>
        [data-table-id="{{ $title }}"] tbody:empty + .data-table-empty { display: flex !important; }
    </style>
    @endif
</div>
