@props(['label', 'value', 'meta' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'bg-surface-raised border border-line rounded-lg p-5 flex flex-col gap-3']) }}>
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-ink-muted">{{ $label }}</span>

        @if ($icon)
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-soft text-brand">
                {!! $icon !!}
            </span>
        @endif
    </div>

    <span class="text-3xl font-semibold tabular-nums">{{ $value }}</span>

    @if ($slot->isNotEmpty())
        <span class="text-sm text-ink-muted">{{ $slot }}</span>
    @elseif ($meta)
        <span class="text-sm text-ink-muted">{{ $meta }}</span>
    @endif
</div>
