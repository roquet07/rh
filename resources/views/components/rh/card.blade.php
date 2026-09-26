@props(['padded' => true])

<div {{ $attributes->merge(['class' => 'bg-surface-raised border border-line rounded-xl shadow-sm '.($padded ? 'p-6' : '')]) }}>
    {{ $slot }}
</div>
