<?php

use App\Models\SolicitudVacante;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Solicitudes de vacante')] class extends Component {
    #[Url]
    public string $estatus = '';

    #[Computed]
    public function solicitudes()
    {
        return SolicitudVacante::query()
            ->with(['departamento', 'puesto', 'solicitante'])
            ->when(! Auth::user()->can('solicitudes.aprobar'), fn ($query) => $query->where('solicitante_user_id', Auth::id()))
            ->when($this->estatus !== '', fn ($query) => $query->where('estatus', $this->estatus))
            ->latest()
            ->get();
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Solicitudes de vacante') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Flujo interno de aprobación de nuevas plazas.') }}</p>
        </div>

        @can('solicitudes.crear')
            <x-rh.button variant="primary" href="{{ route('solicitudes.create') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                {{ __('Nueva solicitud') }}
            </x-rh.button>
        @endcan
    </div>

    <x-rh.select wire:model.live="estatus" name="estatus" placeholder="{{ __('Todos los estatus') }}" class="max-w-xs">
        <option value="pendiente">{{ __('Pendiente') }}</option>
        <option value="aprobada">{{ __('Aprobada') }}</option>
        <option value="rechazada">{{ __('Rechazada') }}</option>
    </x-rh.select>

    <x-rh.card :padded="false">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr>
                    <x-rh.th>{{ __('Folio') }}</x-rh.th>
                    <x-rh.th>{{ __('Departamento') }}</x-rh.th>
                    <x-rh.th>{{ __('Puesto') }}</x-rh.th>
                    <x-rh.th>{{ __('Solicitó') }}</x-rh.th>
                    <x-rh.th>{{ __('Urgencia') }}</x-rh.th>
                    <x-rh.th>{{ __('Estatus') }}</x-rh.th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->solicitudes as $solicitud)
                    <tr class="hover:bg-surface-sunken" wire:key="solicitud-{{ $solicitud->id }}">
                        <x-rh.td class="font-medium">
                            <a href="{{ route('solicitudes.show', $solicitud) }}" wire:navigate class="text-ink hover:underline">{{ $solicitud->folio }}</a>
                        </x-rh.td>
                        <x-rh.td>{{ $solicitud->departamento->nombre }}</x-rh.td>
                        <x-rh.td>{{ $solicitud->puesto->nombre ?? $solicitud->puesto_propuesto }}</x-rh.td>
                        <x-rh.td class="text-ink-muted">{{ $solicitud->solicitante->name }}</x-rh.td>
                        <x-rh.td class="capitalize">{{ $solicitud->urgencia }}</x-rh.td>
                        <x-rh.td><x-rh.badge :estado="$solicitud->estatus" /></x-rh.td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-rh.empty-state
                                title="{{ __('Sin solicitudes') }}"
                                description="{{ __('No hay solicitudes de vacante que coincidan con el filtro.') }}"
                                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.25 3H6.75A1.5 1.5 0 0 0 5.25 4.5v15A1.5 1.5 0 0 0 6.75 21h10.5a1.5 1.5 0 0 0 1.5-1.5V7.5L14.25 3ZM14.25 3v4.5h4.5M12 11.25v6M9 14.25h6"></path></svg>'
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-rh.card>
</section>
