@props([
    'color'   => 'violet',   {{-- violet | success | warning | danger | gray --}}
    'rounded' => 'full',     {{-- full | md --}}
])
@php
    $cls = match($color) {
        'success'        => 'badge-success',
        'warning'        => 'badge-warning',
        'danger', 'error'=> 'badge-danger',
        'gray'           => 'badge-gray',
        default          => 'badge-violet',
    };
    $r = $rounded === 'md' ? 'rounded-md' : 'rounded-full';
@endphp

<span {{ $attributes->merge(['class' => "badge $cls $r"]) }}>
    {{ $slot }}
</span>
