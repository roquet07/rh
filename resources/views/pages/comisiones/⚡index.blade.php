<?php

use App\Models\Comision;
use App\Models\Empleado;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Comisiones')] class extends Component
{
    #[Url]
    public string $filtroEstatus = '';

    public ?int $empleado_id = null;

    public string $monto_base = '';

    public string $porcentaje_aplicado = '';

    public string $fecha_periodo_inicio = '';

    public string $fecha_periodo_fin = '';

    public string $notas = '';

    #[Computed]
    public function empleados()
    {
        return Empleado::query()->where('estatus', 'activo')->with('puesto')->orderBy('nombre_completo')->get();
    }

    #[Computed]
    public function comisiones()
    {
        return Comision::query()
            ->with('empleado')
            ->when($this->filtroEstatus !== '', fn ($query) => $query->where('estatus', $this->filtroEstatus))
            ->latest()
            ->get();
    }

    public function updatedEmpleadoId(): void
    {
        if ($this->empleado_id === null) {
            return;
        }

        $empleado = Empleado::query()->with('puesto')->find($this->empleado_id);

        if ($empleado !== null) {
            $this->porcentaje_aplicado = (string) $empleado->puesto->porcentaje_comision;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'monto_base' => ['required', 'numeric', 'min:0'],
            'porcentaje_aplicado' => ['required', 'numeric', 'min:0', 'max:100'],
            'fecha_periodo_inicio' => ['required', 'date'],
            'fecha_periodo_fin' => ['required', 'date', 'after_or_equal:fecha_periodo_inicio'],
            'notas' => ['nullable', 'string'],
        ]);

        $montoComision = round(((float) $validated['monto_base']) * ((float) $validated['porcentaje_aplicado']) / 100, 2);

        Comision::query()->create($validated + [
            'monto_comision' => $montoComision,
            'estatus' => 'pendiente',
            'capturado_por_user_id' => Auth::id(),
        ]);

        $this->reset('empleado_id', 'monto_base', 'porcentaje_aplicado', 'fecha_periodo_inicio', 'fecha_periodo_fin', 'notas');

        Flux::toast(variant: 'success', text: __('Comisión registrada.'));

        unset($this->comisiones);
    }

    public function marcarPagada(Comision $comision): void
    {
        $this->authorize('comisiones.gestionar');

        $comision->update(['estatus' => 'pagada']);

        Flux::toast(variant: 'success', text: __('Comisión marcada como pagada.'));

        unset($this->comisiones);
    }
}; ?>

<section class="w-full min-w-0 flex flex-col gap-8">
    <div class="flex flex-col gap-1">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Comisiones') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ __('Captura y seguimiento de comisiones por empleado y periodo.') }}</p>
    </div>

    @can('comisiones.gestionar')
        <x-rh.card class="min-w-0">
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Nueva comisión') }}</h2>

            <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <x-rh.select wire:model.live="empleado_id" name="empleado_id" label="{{ __('Empleado') }}" required :error="$errors->first('empleado_id')">
                        @foreach ($this->empleados as $empleado)
                            <option value="{{ $empleado->id }}">{{ $empleado->nombreCompleto() }} ({{ $empleado->puesto->nombre }})</option>
                        @endforeach
                    </x-rh.select>
                </div>

                <x-rh.text-field wire:model="monto_base" name="monto_base" type="number" step="0.01" label="{{ __('Monto base') }}" required prefix="$" :error="$errors->first('monto_base')" />
                <x-rh.text-field wire:model="porcentaje_aplicado" name="porcentaje_aplicado" type="number" step="0.01" label="{{ __('% aplicado') }}" required suffix="%" :error="$errors->first('porcentaje_aplicado')" />
                <x-rh.text-field wire:model="fecha_periodo_inicio" name="fecha_periodo_inicio" type="date" label="{{ __('Inicio del periodo') }}" required :error="$errors->first('fecha_periodo_inicio')" />
                <x-rh.text-field wire:model="fecha_periodo_fin" name="fecha_periodo_fin" type="date" label="{{ __('Fin del periodo') }}" required :error="$errors->first('fecha_periodo_fin')" />

                <div class="col-span-2 flex flex-col gap-1.5">
                    <label for="notas" class="text-sm font-medium text-ink">{{ __('Notas (opcional)') }}</label>
                    <textarea wire:model="notas" id="notas" name="notas" rows="2" class="w-full rounded-md border border-line-strong bg-surface-raised px-3 py-2 text-sm text-ink focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus"></textarea>
                </div>

                <div class="col-span-2 flex justify-end">
                    <x-rh.button type="submit" variant="primary">{{ __('Registrar comisión') }}</x-rh.button>
                </div>
            </form>
        </x-rh.card>
    @endcan

    <x-rh.select wire:model.live="filtroEstatus" name="filtroEstatus" placeholder="{{ __('Todos los estatus') }}" class="max-w-xs">
        <option value="pendiente">{{ __('Pendiente') }}</option>
        <option value="pagada">{{ __('Pagada') }}</option>
    </x-rh.select>

    @if ($this->comisiones->isEmpty())
        <x-rh.card :padded="false" class="min-w-0">
            <x-rh.empty-state
                title="{{ __('Sin comisiones') }}"
                description="{{ __('Aún no se han registrado comisiones.') }}"
                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2.25 6.75h19.5v10.5H2.25zM14.25 12a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM5.25 9.75v4.5M18.75 9.75v4.5"></path></svg>'
            />
        </x-rh.card>
    @else
        {{-- Mobile: card list, no horizontal scroll --}}
        <div class="flex flex-col gap-3 md:hidden">
            @foreach ($this->comisiones as $comision)
                <x-rh.card class="min-w-0" wire:key="comision-mobile-{{ $comision->id }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="m-0 font-medium text-ink truncate">{{ $comision->empleado->nombreCompleto() }}</p>
                            <p class="m-0 text-xs text-ink-muted">{{ $comision->fecha_periodo_inicio->translatedFormat('d M Y') }} – {{ $comision->fecha_periodo_fin->translatedFormat('d M Y') }}</p>
                        </div>
                        <x-rh.badge :estado="$comision->estatus" />
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <dt class="text-xs text-ink-muted">{{ __('Monto base') }}</dt>
                            <dd class="m-0 tabular-nums">${{ number_format((float) $comision->monto_base, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-ink-muted">{{ __('%') }}</dt>
                            <dd class="m-0 tabular-nums">{{ number_format((float) $comision->porcentaje_aplicado, 2) }}%</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs text-ink-muted">{{ __('Comisión') }}</dt>
                            <dd class="m-0 tabular-nums font-medium">${{ number_format((float) $comision->monto_comision, 2) }}</dd>
                        </div>
                    </dl>

                    @can('comisiones.gestionar')
                        @if ($comision->estatus === 'pendiente')
                            <button type="button" wire:click="marcarPagada({{ $comision->id }})" class="mt-3 text-sm font-medium text-brand hover:underline cursor-pointer">{{ __('Marcar pagada') }}</button>
                        @endif
                    @endcan
                </x-rh.card>
            @endforeach
        </div>

        {{-- Desktop/tablet: table --}}
        <x-rh.card :padded="false" class="hidden md:block min-w-0">
            <div class="overflow-x-auto">
            <table class="w-full min-w-max border-collapse text-sm">
                <thead>
                    <tr>
                        <x-rh.th>{{ __('Empleado') }}</x-rh.th>
                        <x-rh.th>{{ __('Periodo') }}</x-rh.th>
                        <x-rh.th>{{ __('Monto base') }}</x-rh.th>
                        <x-rh.th>{{ __('%') }}</x-rh.th>
                        <x-rh.th>{{ __('Comisión') }}</x-rh.th>
                        <x-rh.th>{{ __('Estatus') }}</x-rh.th>
                        @can('comisiones.gestionar')
                            <x-rh.th sr-only>{{ __('Acciones') }}</x-rh.th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->comisiones as $comision)
                        <tr class="hover:bg-surface-sunken" wire:key="comision-{{ $comision->id }}">
                            <x-rh.td>{{ $comision->empleado->nombreCompleto() }}</x-rh.td>
                            <x-rh.td class="text-ink-muted whitespace-nowrap">{{ $comision->fecha_periodo_inicio->translatedFormat('d M Y') }} — {{ $comision->fecha_periodo_fin->translatedFormat('d M Y') }}</x-rh.td>
                            <x-rh.td class="tabular-nums">${{ number_format((float) $comision->monto_base, 2) }}</x-rh.td>
                            <x-rh.td class="tabular-nums">{{ number_format((float) $comision->porcentaje_aplicado, 2) }}%</x-rh.td>
                            <x-rh.td class="tabular-nums font-medium">${{ number_format((float) $comision->monto_comision, 2) }}</x-rh.td>
                            <x-rh.td><x-rh.badge :estado="$comision->estatus" /></x-rh.td>
                            @can('comisiones.gestionar')
                                <x-rh.td class="text-right">
                                    @if ($comision->estatus === 'pendiente')
                                        <button type="button" wire:click="marcarPagada({{ $comision->id }})" class="text-sm font-medium text-brand hover:underline cursor-pointer">{{ __('Marcar pagada') }}</button>
                                    @endif
                                </x-rh.td>
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </x-rh.card>
    @endif
</section>
