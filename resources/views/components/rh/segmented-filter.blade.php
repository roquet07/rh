@props(['options', 'active', 'property'])

<div role="group" aria-label="Filtrar por estatus" class="flex gap-1 rounded-lg bg-surface-sunken p-1 max-w-full overflow-x-auto">
    @foreach ($options as $value => $option)
        @php($isActive = $active === $value)

        <button
            type="button"
            wire:click="$set('{{ $property }}', '{{ $value }}')"
            aria-pressed="{{ $isActive ? 'true' : 'false' }}"
            class="inline-flex h-8 items-center gap-2 rounded-md px-3 text-sm font-medium whitespace-nowrap cursor-pointer {{ $isActive ? 'bg-surface-raised text-ink shadow-sm' : 'text-ink-muted hover:bg-surface-raised/50' }}"
        >
            {{ $option['label'] }}
            <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-surface text-ink-muted text-xs tabular-nums flex items-center justify-center">
                {{ $option['count'] }}
            </span>
        </button>
    @endforeach
</div>
