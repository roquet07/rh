<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="p-4 sm:p-8 flex flex-col gap-8 bg-surface overflow-x-clip min-w-0">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
