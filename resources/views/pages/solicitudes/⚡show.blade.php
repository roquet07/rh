<?php

use App\Actions\SolicitudesVacante\AprobarSolicitudVacante;
use App\Actions\SolicitudesVacante\RechazarSolicitudVacante;
use App\Models\SolicitudVacante;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Solicitud de vacante')] class extends Component {
    public SolicitudVacante $solicitud;

    public string $comentario_revision = '';

    public function mount(SolicitudVacante $solicitud): void
    {
        $this->solicitud = $solicitud->load(['departamento', 'puesto', 'solicitante', 'revisadoPor']);
    }

    public function aprobar(AprobarSolicitudVacante $aprobar): void
    {
        $this->authorize('solicitudes.aprobar');

        $aprobar($this->solicitud, Auth::user(), $this->comentario_revision ?: null);

        $this->solicitud->refresh();

        Flux::toast(variant: 'success', text: __('Solicitud aprobada.'));
    }

    public function rechazar(RechazarSolicitudVacante $rechazar): void
    {
        $this->authorize('solicitudes.aprobar');

        $rechazar($this->solicitud, Auth::user(), $this->comentario_revision ?: null);

        $this->solicitud->refresh();

        Flux::toast(variant: 'success', text: __('Solicitud rechazada.'));
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ $solicitud->folio }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Solicitado por') }} {{ $solicitud->solicitante->name }}</p>
        </div>

        <x-rh.badge :estado="$solicitud->estatus" />
    </div>

    <x-rh.card>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div><dt class="text-ink-muted">{{ __('Departamento') }}</dt><dd class="m-0">{{ $solicitud->departamento->nombre }}</dd></div>
            <div><dt class="text-ink-muted">{{ __('Puesto') }}</dt><dd class="m-0">{{ $solicitud->puesto->nombre ?? $solicitud->puesto_propuesto }}</dd></div>
            <div><dt class="text-ink-muted">{{ __('Salario propuesto') }}</dt><dd class="m-0 tabular-nums">${{ number_format((float) $solicitud->salario_propuesto, 2) }}</dd></div>
            <div><dt class="text-ink-muted">{{ __('Plazas') }}</dt><dd class="m-0 tabular-nums">{{ $solicitud->numero_plazas }}</dd></div>
            <div><dt class="text-ink-muted">{{ __('Urgencia') }}</dt><dd class="m-0 capitalize">{{ $solicitud->urgencia }}</dd></div>
            <div class="col-span-2"><dt class="text-ink-muted">{{ __('Justificación') }}</dt><dd class="m-0">{{ $solicitud->justificacion }}</dd></div>

            @if ($solicitud->estatus !== 'pendiente')
                <div><dt class="text-ink-muted">{{ __('Revisado por') }}</dt><dd class="m-0">{{ $solicitud->revisadoPor?->name }}</dd></div>
                <div><dt class="text-ink-muted">{{ __('Fecha de revisión') }}</dt><dd class="m-0">{{ $solicitud->fecha_revision?->translatedFormat('d M Y H:i') }}</dd></div>
                @if ($solicitud->comentario_revision)
                    <div class="col-span-2"><dt class="text-ink-muted">{{ __('Comentario') }}</dt><dd class="m-0">{{ $solicitud->comentario_revision }}</dd></div>
                @endif
            @endif
        </dl>
    </x-rh.card>

    @can('solicitudes.aprobar')
        @if ($solicitud->estatus === 'pendiente')
            <x-rh.card>
                <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Revisión') }}</h2>

                <div class="flex flex-col gap-1.5 mb-4">
                    <label for="comentario_revision" class="text-sm font-medium text-ink">{{ __('Comentario (opcional)') }}</label>
                    <textarea wire:model="comentario_revision" id="comentario_revision" name="comentario_revision" rows="3" class="w-full rounded-md border border-line-strong bg-surface-raised px-3 py-2 text-sm text-ink focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus"></textarea>
                </div>

                <div class="flex gap-2">
                    <x-rh.button variant="danger" wire:click="rechazar" wire:confirm="{{ __('¿Rechazar esta solicitud?') }}">{{ __('Rechazar') }}</x-rh.button>
                    <x-rh.button variant="primary" wire:click="aprobar">{{ __('Aprobar') }}</x-rh.button>
                </div>
            </x-rh.card>
        @endif
    @endcan

    @can('empleados.gestionar')
        @if ($solicitud->estatus === 'aprobada')
            <x-rh.card>
                <h2 class="m-0 text-lg font-semibold">{{ __('Siguiente paso') }}</h2>
                <p class="m-0 mt-1 mb-4 text-sm text-ink-muted">{{ __('Esta solicitud ya fue aprobada. Puedes crear el empleado a partir de ella.') }}</p>

                <x-rh.button variant="primary" href="{{ route('empleados.create', ['solicitud' => $solicitud->id]) }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 7.5v6m3-3h-6M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"></path></svg>
                    {{ __('Crear empleado desde esta solicitud') }}
                </x-rh.button>
            </x-rh.card>
        @endif
    @endcan
</section>
