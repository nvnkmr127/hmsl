@props([
    'status' => 'Pending',
    'size'   => 'md',
])

@php
    $statusThemes = [
        'Pending'   => ['bg' => 'bg-amber-100 text-amber-600', 'label' => 'Waiting', 'icon' => 'M12 8v4l3 3'],
        'Ongoing'   => ['bg' => 'bg-indigo-100 text-indigo-600', 'label' => 'Ongoing', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        'Completed' => ['bg' => 'bg-emerald-100 text-emerald-600', 'label' => 'Done', 'icon' => 'M9 12l2 2 4-4'],
        'Cancelled' => ['bg' => 'bg-rose-100 text-rose-600', 'label' => 'Cancelled', 'icon' => 'M6 18L18 6M6 6l12 12'],
        'Admitted'  => ['bg' => 'bg-indigo-100 text-indigo-600', 'label' => 'Admitted', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16'],
        'Discharged'=> ['bg' => 'bg-gray-100 text-gray-600', 'label' => 'Discharged', 'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7'],
    ];

    $theme = $statusThemes[$status] ?? ['bg' => 'bg-gray-100 text-gray-600', 'label' => $status, 'icon' => ''];
    $px = $size === 'sm' ? 'px-2 py-0.5' : 'px-4 py-1.5';
    $text = $size === 'sm' ? 'text-dense' : 'text-tiny';
@endphp

<span {{ $attributes->merge(['class' => "$theme[bg] $px $text rounded-xl font-black uppercase tracking-[0.1em] inline-flex items-center gap-1.5 ring-4 ring-white dark:ring-gray-950"]) }}>
    @if($theme['icon'])
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $theme['icon'] }}" /></svg>
    @endif
    {{ $theme['label'] }}
</span>
