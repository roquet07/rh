@props(['srOnly' => false])

<th scope="col" {{ $attributes->merge(['class' => 'px-4 py-2.5 bg-surface-sunken border-b border-line text-left text-xs font-medium uppercase tracking-wide text-ink-muted whitespace-nowrap']) }}>
    @if ($srOnly)
        <span class="sr-only">{{ $slot }}</span>
    @else
        {{ $slot }}
    @endif
</th>
