<?php

use App\Models\PeriodoNomina;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Periodos de nómina')] class extends Component {
    public string $tipo = 'quincenal';

    public string $fecha_inicio = '';

    public string $fecha_fin = '';

    public string $fecha_pago = '';

    #[Computed]
    public function periodos()
    {
        return PeriodoNomina::query()->orderByDesc('fecha_inicio')->get();
    }

    public function create(): void
    {
        $validated = $this->validate([
            'tipo' => ['required', Rule::in(['semanal', 'quincenal', 'mensual'])],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'fecha_pago' => ['required', 'date', 'after_or_equal:fecha_fin'],
        ]);

        $periodo = PeriodoNomina::query()->create($validated + ['estatus' => 'abierto']);

        Flux::toast(variant: 'success', text: __('Periodo de nómina creado.'));

        $this->redirect(route('nomina.periodo-detalle', $periodo), navigate: true);
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex flex-col gap-1">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Nómina') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ __('Periodos de pago y cálculo de percepciones/deducciones.') }}</p>
    </div>

    @can('nomina.gestionar')
        <x-rh.card>
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Nuevo periodo') }}</h2>

            <form wire:submit="create" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-rh.select wire:model="tipo" name="tipo" label="{{ __('Tipo') }}" required>
                    <option value="semanal">{{ __('Semanal') }}</option>
                    <option value="quincenal">{{ __('Quincenal') }}</option>
                    <option value="mensual">{{ __('Mensual') }}</option>
                </x-rh.select>

                <div></div>

                <x-rh.text-field wire:model="fecha_inicio" name="fecha_inicio" type="date" label="{{ __('Fecha de inicio') }}" required :error="$errors->first('fecha_inicio')" />
                <x-rh.text-field wire:model="fecha_fin" name="fecha_fin" type="date" label="{{ __('Fecha de fin') }}" required :error="$errors->first('fecha_fin')" />
                <x-rh.text-field wire:model="fecha_pago" name="fecha_pago" type="date" label="{{ __('Fecha de pago') }}" required :error="$errors->first('fecha_pago')" />

                <div class="col-span-2 flex justify-end">
                    <x-rh.button type="submit" variant="primary">{{ __('Crear periodo') }}</x-rh.button>
                </div>
            </form>
        </x-rh.card>
    @endcan

    <x-rh.card :padded="false">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr>
                    <x-rh.th>{{ __('Tipo') }}</x-rh.th>
                    <x-rh.th>{{ __('Periodo') }}</x-rh.th>
                    <x-rh.th>{{ __('Fecha de pago') }}</x-rh.th>
                    <x-rh.th>{{ __('Estatus') }}</x-rh.th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->periodos as $periodo)
                    <tr class="hover:bg-surface-sunken" wire:key="periodo-{{ $periodo->id }}">
                        <x-rh.td class="capitalize">{{ $periodo->tipo }}</x-rh.td>
                        <x-rh.td>
                            <a href="{{ route('nomina.periodo-detalle', $periodo) }}" wire:navigate class="text-ink hover:underline">
                                {{ $periodo->fecha_inicio->translatedFormat('d M Y') }} — {{ $periodo->fecha_fin->translatedFormat('d M Y') }}
                            </a>
                        </x-rh.td>
                        <x-rh.td class="text-ink-muted whitespace-nowrap">{{ $periodo->fecha_pago->translatedFormat('d M Y') }}</x-rh.td>
                        <x-rh.td><x-rh.badge :estado="$periodo->estatus" /></x-rh.td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-rh.empty-state
                                title="{{ __('Sin periodos') }}"
                                description="{{ __('Aún no se han creado periodos de nómina.') }}"
                                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6.75 3h10.5a1.5 1.5 0 0 1 1.5 1.5v15a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-15A1.5 1.5 0 0 1 6.75 3ZM8.25 6.75h7.5v3h-7.5zM8.25 13.5h.01M12 13.5h.01M15.75 13.5h.01M8.25 17.25h.01M12 17.25h.01M15.75 17.25h.01"></path></svg>'
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-rh.card>
</section>
