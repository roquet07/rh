@props(['icon', 'title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-2 py-12 px-6 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-surface-sunken text-ink-muted">
        {!! $icon !!}
    </span>

    <p class="text-base font-semibold">{{ $title }}</p>

    @if ($description)
        <p class="text-sm text-ink-muted">{{ $description }}</p>
    @endif

    {{ $slot ?? '' }}
</div>
