@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'bg-brand text-on-brand border border-transparent hover:bg-brand-hover',
        'secondary' => 'bg-surface-raised text-ink border border-line-strong hover:bg-surface-sunken',
        'ghost' => 'bg-transparent text-ink-muted border border-transparent hover:bg-surface-sunken hover:text-ink',
        'danger' => 'bg-danger text-white border border-transparent hover:opacity-90',
    ];

    $height = $size === 'lg' ? 'h-11' : ($size === 'sm' ? 'h-8 px-3 text-sm' : 'h-10 px-4');

    $classes = 'inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium whitespace-nowrap cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed '.$height.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
