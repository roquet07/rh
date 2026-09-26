@props([
    'name' => '',
    'photoUrl' => null,
    'size' => 'md',
])

@php
    $iniciales = collect(explode(' ', trim($name)))
        ->filter()
        ->map(fn ($palabra) => mb_strtoupper(mb_substr($palabra, 0, 1)))
        ->take(2)
        ->implode('');

    $dimension = $size === 'lg' ? 'h-10 w-10 text-sm' : ($size === 'sm' ? 'h-8 w-8 text-xs' : 'h-9 w-9 text-[13px]');
@endphp

@if ($photoUrl)
    <img src="{{ $photoUrl }}" alt="" aria-hidden="true" {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover '.$dimension]) }}>
@else
    <span aria-hidden="true" {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center rounded-full bg-warm font-semibold text-on-warm '.$dimension]) }}>
        {{ $iniciales }}
    </span>
@endif
