<?php

use App\Models\Contrato;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Contratos')] class extends Component {
    #[Computed]
    public function contratos()
    {
        return Contrato::query()->with('empleado', 'puesto')->latest()->get();
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex flex-col gap-1">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Contratos') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ __('Contratos individuales de trabajo generados en el sistema.') }}</p>
    </div>

    <x-rh.card :padded="false">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr>
                    <x-rh.th>{{ __('Empleado') }}</x-rh.th>
                    <x-rh.th>{{ __('Puesto') }}</x-rh.th>
                    <x-rh.th>{{ __('Tipo') }}</x-rh.th>
                    <x-rh.th>{{ __('Vigencia') }}</x-rh.th>
                    <x-rh.th>{{ __('Estatus') }}</x-rh.th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->contratos as $contrato)
                    <tr class="hover:bg-surface-sunken" wire:key="contrato-{{ $contrato->id }}">
                        <x-rh.td class="font-medium">
                            <a href="{{ route('contratos.show', $contrato) }}" wire:navigate class="text-ink hover:underline">{{ $contrato->empleado->nombreCompleto() }}</a>
                        </x-rh.td>
                        <x-rh.td>{{ $contrato->puesto->nombre }}</x-rh.td>
                        <x-rh.td class="capitalize">{{ str_replace('_', ' ', $contrato->tipo) }}</x-rh.td>
                        <x-rh.td class="text-ink-muted whitespace-nowrap">{{ $contrato->fecha_inicio->translatedFormat('d M Y') }} — {{ $contrato->fecha_fin?->translatedFormat('d M Y') ?? __('indefinido') }}</x-rh.td>
                        <x-rh.td><x-rh.badge :estado="$contrato->estatus" /></x-rh.td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-rh.empty-state
                                title="{{ __('Sin contratos') }}"
                                description="{{ __('Aún no se han generado contratos.') }}"
                                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.25 3H6.75A1.5 1.5 0 0 0 5.25 4.5v15A1.5 1.5 0 0 0 6.75 21h10.5a1.5 1.5 0 0 0 1.5-1.5V7.5L14.25 3ZM14.25 3v4.5h4.5M9 12.75h6M9 15.75h6"></path></svg>'
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-rh.card>
</section>
