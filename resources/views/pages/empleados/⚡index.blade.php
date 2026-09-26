<?php

use App\Models\Departamento;
use App\Models\Empleado;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Empleados')] class extends Component {
    use WithPagination;

    #[Url]
    public string $buscar = '';

    #[Url]
    public string $estado = 'Todos';

    #[Url]
    public string $departamento_id = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function updatingDepartamentoId(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset('buscar', 'estado', 'departamento_id');
    }

    private function aplicarFiltros($query)
    {
        return $query
            ->when($this->buscar !== '', fn ($q) => $q->where(function ($q) {
                $q->where('nombre_completo', 'like', "%{$this->buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$this->buscar}%")
                    ->orWhere('numero_empleado', 'like', "%{$this->buscar}%")
                    ->orWhere('rfc', 'like', "%{$this->buscar}%");
            }))
            ->when($this->departamento_id !== '', fn ($q) => $q->whereHas('puesto', fn ($q) => $q->where('departamento_id', $this->departamento_id)))
            ->when($this->estado === 'Activo', fn ($q) => $q->where('estatus', 'activo')->whereDoesntHave('contratos', fn ($q) => $q->where('tipo', 'periodo_prueba')->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now()->toDateString()))))
            ->when($this->estado === 'Periodo de prueba', fn ($q) => $q->where('estatus', 'activo')->whereHas('contratos', fn ($q) => $q->where('tipo', 'periodo_prueba')->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now()->toDateString()))))
            ->when($this->estado === 'Documentación pendiente', fn ($q) => $q->where('estatus', 'documentacion_pendiente'))
            ->when($this->estado === 'Baja', fn ($q) => $q->where('estatus', 'baja'));
    }

    #[Computed]
    public function empleados()
    {
        return $this->aplicarFiltros(Empleado::query()->with('puesto.departamento'))
            ->orderBy('nombre_completo')
            ->paginate(15);
    }

    #[Computed]
    public function departamentos()
    {
        return Departamento::query()->where('activo', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function conteos()
    {
        $base = fn () => Empleado::query();

        return [
            'Todos' => Empleado::query()->count(),
            'Activo' => (clone $this->aplicarFiltros($base()))->where('estatus', 'activo')->count(),
            'Periodo de prueba' => Empleado::query()->where('estatus', 'activo')->whereHas('contratos', fn ($q) => $q->where('tipo', 'periodo_prueba')->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now()->toDateString())))->count(),
            'Documentación pendiente' => Empleado::query()->where('estatus', 'documentacion_pendiente')->count(),
            'Baja' => Empleado::query()->where('estatus', 'baja')->count(),
        ];
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-end justify-between gap-6">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Empleados') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Expedientes, puestos y estatus laboral.') }}</p>
        </div>

        <div class="flex gap-2">
            <x-rh.button variant="secondary" href="{{ route('empleados.exportar', request()->query()) }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"></path></svg>
                {{ __('Exportar') }}
            </x-rh.button>

            @can('empleados.gestionar')
                <x-rh.button variant="primary" href="{{ route('empleados.create') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                    {{ __('Nuevo empleado') }}
                </x-rh.button>
            @endcan
        </div>
    </div>

    <x-rh.card :padded="false">
        <div class="flex flex-wrap items-center justify-between gap-4 p-4">
            <x-rh.segmented-filter
                property="estado"
                :active="$estado"
                :options="[
                    'Todos' => ['label' => __('Todos'), 'count' => $this->conteos['Todos']],
                    'Activo' => ['label' => __('Activo'), 'count' => $this->conteos['Activo']],
                    'Periodo de prueba' => ['label' => __('Periodo de prueba'), 'count' => $this->conteos['Periodo de prueba']],
                    'Documentación pendiente' => ['label' => __('Documentación pendiente'), 'count' => $this->conteos['Documentación pendiente']],
                    'Baja' => ['label' => __('Baja'), 'count' => $this->conteos['Baja']],
                ]"
            />

            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <label class="relative flex items-center w-full sm:w-[320px]">
                    <span aria-hidden="true" class="absolute left-3 flex text-ink-muted">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path></svg>
                    </span>
                    <span class="sr-only">{{ __('Buscar empleados') }}</span>
                    <input type="search" wire:model.live.debounce.300ms="buscar" placeholder="{{ __('Buscar por nombre, número o RFC') }}" class="h-10 w-full rounded-md border border-line-strong bg-surface-raised pl-10 pr-3 text-sm text-ink placeholder:text-ink-muted focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus">
                </label>

                <label class="flex">
                    <span class="sr-only">{{ __('Departamento') }}</span>
                    <select wire:model.live="departamento_id" class="h-10 w-full sm:w-[220px] rounded-md border border-line-strong bg-surface-raised px-3 text-sm text-ink focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus">
                        <option value="">{{ __('Todos los departamentos') }}</option>
                        @foreach ($this->departamentos as $departamento)
                            <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <x-rh.th class="w-12">
                            <span class="sr-only">{{ __('Seleccionar todos') }}</span>
                            <input type="checkbox" aria-label="{{ __('Seleccionar todos') }}" class="h-4 w-4 accent-brand">
                        </x-rh.th>
                        <x-rh.th>{{ __('Número') }}</x-rh.th>
                        <x-rh.th>{{ __('Colaborador') }}</x-rh.th>
                        <x-rh.th>{{ __('Puesto') }}</x-rh.th>
                        <x-rh.th>{{ __('Departamento') }}</x-rh.th>
                        <x-rh.th>{{ __('Ingreso') }}</x-rh.th>
                        <x-rh.th>{{ __('Estatus') }}</x-rh.th>
                        <x-rh.th class="w-14" sr-only>{{ __('Acciones') }}</x-rh.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->empleados as $empleado)
                        <tr class="hover:bg-surface-sunken" wire:key="empleado-{{ $empleado->id }}">
                            <x-rh.td>
                                <input type="checkbox" aria-label="{{ __('Seleccionar :nombre', ['nombre' => $empleado->nombreCompleto()]) }}" class="h-4 w-4 accent-brand">
                            </x-rh.td>
                            <x-rh.td class="whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-mono">{{ $empleado->numero_empleado }}</span>
                                    <span class="font-mono text-xs text-ink-muted">{{ $empleado->rfc }}</span>
                                </div>
                            </x-rh.td>
                            <x-rh.td>
                                <div class="flex items-center gap-3">
                                    <x-rh.avatar :name="$empleado->nombreCompleto()" :photo-url="$empleado->foto_path ? route('empleados.foto', $empleado) : null" />
                                    <div class="flex flex-col min-w-0">
                                        <a href="{{ route('empleados.show', $empleado) }}" wire:navigate class="font-medium text-ink hover:underline">{{ $empleado->nombreCompleto() }}</a>
                                        <span class="text-xs text-ink-muted truncate">{{ $empleado->correo_personal ?: '—' }}</span>
                                    </div>
                                </div>
                            </x-rh.td>
                            <x-rh.td>{{ $empleado->puesto->nombre }}</x-rh.td>
                            <x-rh.td class="text-ink-muted">{{ $empleado->puesto->departamento->nombre }}</x-rh.td>
                            <x-rh.td class="text-ink-muted whitespace-nowrap">{{ $empleado->fecha_ingreso->translatedFormat('d M Y') }}</x-rh.td>
                            <x-rh.td><x-rh.badge :estado="$empleado->estadoLaboral()" /></x-rh.td>
                            <x-rh.td class="text-right">
                                <div x-data="{ open: false }" class="relative inline-block">
                                    <button type="button" @click="open = !open" @click.outside="open = false" aria-label="{{ __('Más acciones para :nombre', ['nombre' => $empleado->nombreCompleto()]) }}" class="flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-ink cursor-pointer">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 12h.01M12 12h.01M17.25 12h.01"></path></svg>
                                    </button>

                                    <div x-show="open" x-cloak x-transition class="absolute right-0 z-10 mt-1 w-40 rounded-md border border-line bg-surface-raised py-1 shadow-md">
                                        <a href="{{ route('empleados.show', $empleado) }}" wire:navigate class="block px-3 py-2 text-sm text-ink hover:bg-surface-sunken">{{ __('Ver') }}</a>
                                        @can('empleados.gestionar')
                                            <a href="{{ route('empleados.edit', $empleado) }}" wire:navigate class="block px-3 py-2 text-sm text-ink hover:bg-surface-sunken">{{ __('Editar') }}</a>
                                            @if ($empleado->estatus === 'activo')
                                                <a href="{{ route('empleados.edit', $empleado) }}" wire:navigate class="block px-3 py-2 text-sm text-danger hover:bg-surface-sunken">{{ __('Dar de baja') }}</a>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </x-rh.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-rh.empty-state
                                    title="{{ __('Sin resultados') }}"
                                    description="{{ __('Ningún colaborador coincide con la búsqueda o los filtros.') }}"
                                    icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path></svg>'
                                >
                                    <x-rh.button variant="secondary" wire:click="limpiarFiltros">{{ __('Limpiar filtros') }}</x-rh.button>
                                </x-rh.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->empleados->total() > 0)
            <div class="flex items-center justify-between px-4 py-3 text-sm text-ink-muted">
                <span>{{ __('Mostrando') }} <strong class="text-ink font-medium">{{ $this->empleados->count() }}</strong> {{ __('de :total colaboradores', ['total' => $this->empleados->total()]) }}</span>

                {{ $this->empleados->onEachSide(1)->links() }}
            </div>
        @endif
    </x-rh.card>
</section>
