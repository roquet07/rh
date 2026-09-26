<?php

use App\Actions\Nomina\CalcularPeriodoNomina;
use App\Actions\Nomina\GenerarReciboNomina;
use App\Models\NominaDetalle;
use App\Models\PeriodoNomina;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Detalle de periodo de nómina')] class extends Component {
    public PeriodoNomina $periodo;

    public function mount(PeriodoNomina $periodo): void
    {
        $this->periodo = $periodo->load(['detalles.empleado']);
    }

    public function calcular(CalcularPeriodoNomina $calcular): void
    {
        $this->authorize('nomina.gestionar');

        $calcular->calcular($this->periodo);

        $this->periodo->refresh()->load(['detalles.empleado']);

        Flux::toast(variant: 'success', text: __('Nómina calculada para el periodo.'));
    }

    public function generarRecibo(int $detalleId, GenerarReciboNomina $generar): void
    {
        $this->authorize('nomina.gestionar');

        $detalle = NominaDetalle::query()->findOrFail($detalleId);

        $generar->generar($detalle);

        $this->periodo->refresh()->load(['detalles.empleado']);

        Flux::toast(variant: 'success', text: __('Recibo generado.'));
    }

    public function descargarRecibo(int $detalleId)
    {
        $detalle = NominaDetalle::query()->findOrFail($detalleId);

        abort_unless($detalle->recibo_pdf_path, 404);

        return Storage::disk('local')->download($detalle->recibo_pdf_path, "recibo-{$detalle->empleado->numero_empleado}.pdf");
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Periodo') }} {{ $periodo->fecha_inicio->translatedFormat('d M Y') }} — {{ $periodo->fecha_fin->translatedFormat('d M Y') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Fecha de pago') }}: {{ $periodo->fecha_pago->translatedFormat('d M Y') }}</p>
        </div>

        @can('nomina.gestionar')
            <x-rh.button variant="primary" wire:click="calcular">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 7.5h6M9 7.5V6a1.5 1.5 0 0 0-1.5-1.5h-3A1.5 1.5 0 0 0 3 6v13.5A1.5 1.5 0 0 0 4.5 21h15a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5h-3A1.5 1.5 0 0 0 15 6v1.5m-6 0h6m-9 4.5h.008v.008H6V12Zm3 0h.008v.008H9V12Zm3 0h.008v.008H12V12Zm3 0h.008v.008H15V12Zm-9 3h.008v.008H6V15Zm3 0h.008v.008H9V15Zm3 0h.008v.008H12V15Zm3 0h.008v.008H15V15Z"></path></svg>
                {{ $periodo->detalles->isEmpty() ? __('Calcular nómina') : __('Recalcular nómina') }}
            </x-rh.button>
        @endcan
    </div>

    <div class="flex gap-3 px-4 py-3 rounded-lg bg-info-soft text-info text-sm">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"></path></svg>
        <span>{{ __('El cálculo de IMSS es un estimado simplificado y no sustituye el cálculo oficial del IMSS. Los recibos generados son documentos administrativos internos, no CFDI de nómina 4.0 timbrado ante el SAT.') }}</span>
    </div>

    <x-rh.card :padded="false">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr>
                    <x-rh.th>{{ __('Empleado') }}</x-rh.th>
                    <x-rh.th>{{ __('Días') }}</x-rh.th>
                    <x-rh.th>{{ __('Percepciones') }}</x-rh.th>
                    <x-rh.th>{{ __('Deducciones') }}</x-rh.th>
                    <x-rh.th>{{ __('Neto a pagar') }}</x-rh.th>
                    <x-rh.th sr-only>{{ __('Acciones') }}</x-rh.th>
                </tr>
            </thead>
            <tbody>
                @forelse ($periodo->detalles as $detalle)
                    <tr class="hover:bg-surface-sunken" wire:key="detalle-{{ $detalle->id }}">
                        <x-rh.td class="font-medium">{{ $detalle->empleado->nombreCompleto() }}</x-rh.td>
                        <x-rh.td class="tabular-nums">{{ $detalle->dias_trabajados }}</x-rh.td>
                        <x-rh.td class="tabular-nums">${{ number_format((float) $detalle->total_percepciones, 2) }}</x-rh.td>
                        <x-rh.td class="tabular-nums">${{ number_format((float) $detalle->total_deducciones, 2) }}</x-rh.td>
                        <x-rh.td class="tabular-nums font-medium">${{ number_format((float) $detalle->neto_pagar, 2) }}</x-rh.td>
                        <x-rh.td class="text-right">
                            <div class="flex justify-end gap-3">
                                @can('nomina.gestionar')
                                    <button type="button" wire:click="generarRecibo({{ $detalle->id }})" class="text-sm font-medium text-brand hover:underline cursor-pointer">
                                        {{ $detalle->recibo_pdf_path ? __('Regenerar') : __('Generar recibo') }}
                                    </button>
                                @endcan

                                @if ($detalle->recibo_pdf_path)
                                    <button type="button" wire:click="descargarRecibo({{ $detalle->id }})" class="text-sm font-medium text-brand hover:underline cursor-pointer">{{ __('Descargar') }}</button>
                                @endif
                            </div>
                        </x-rh.td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-rh.empty-state
                                title="{{ __('Aún no calculado') }}"
                                description="{{ __('Aún no se ha calculado este periodo.') }}"
                                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6.75 3h10.5a1.5 1.5 0 0 1 1.5 1.5v15a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-15A1.5 1.5 0 0 1 6.75 3Z"></path></svg>'
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-rh.card>
</section>
