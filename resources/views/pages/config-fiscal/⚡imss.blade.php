<?php

use App\Models\ImssRate;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tasas IMSS')] class extends Component {
    public int $ejercicio_fiscal;

    public string $uma_diaria = '';

    public string $porcentaje_obrero = '';

    public string $porcentaje_patronal = '';

    public int $tope_sbc_umas = 25;

    public string $notas = '';

    public function mount(): void
    {
        $this->ejercicio_fiscal = now()->year;
        $this->cargar();
    }

    public function updatedEjercicioFiscal(): void
    {
        $this->cargar();
    }

    private function cargar(): void
    {
        $tasa = ImssRate::query()->where('ejercicio_fiscal', $this->ejercicio_fiscal)->first();

        $this->uma_diaria = (string) ($tasa->uma_diaria ?? '');
        $this->porcentaje_obrero = (string) ($tasa->porcentaje_obrero ?? '');
        $this->porcentaje_patronal = (string) ($tasa->porcentaje_patronal ?? '');
        $this->tope_sbc_umas = $tasa->tope_sbc_umas ?? 25;
        $this->notas = (string) ($tasa->notas ?? '');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'uma_diaria' => ['required', 'numeric', 'min:0'],
            'porcentaje_obrero' => ['required', 'numeric', 'min:0', 'max:100'],
            'porcentaje_patronal' => ['required', 'numeric', 'min:0', 'max:100'],
            'tope_sbc_umas' => ['required', 'integer', 'min:1'],
            'notas' => ['nullable', 'string'],
        ]);

        ImssRate::query()->updateOrCreate(
            ['ejercicio_fiscal' => $this->ejercicio_fiscal],
            $validated,
        );

        Flux::toast(variant: 'success', text: __('Tasas IMSS actualizadas.'));
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl">{{ __('Configuración fiscal — IMSS') }}</flux:heading>
    <flux:subheading>{{ __('Valores usados para el cálculo estimado de la cuota obrera del IMSS.') }}</flux:subheading>

    <flux:callout class="mt-4" variant="warning" icon="exclamation-triangle">
        {{ __('Este cálculo es un estimado simplificado y no sustituye el cálculo oficial del IMSS, que considera múltiples ramos de aseguramiento e INFONAVIT.') }}
    </flux:callout>

    <form wire:submit="save" class="mt-6 max-w-lg space-y-6">
        <flux:input wire:model="ejercicio_fiscal" :label="__('Ejercicio fiscal')" type="number" required />
        <flux:input wire:model="uma_diaria" :label="__('UMA diaria')" type="number" step="0.01" required />
        <flux:input wire:model="porcentaje_obrero" :label="__('% obrero (agregado)')" type="number" step="0.0001" required />
        <flux:input wire:model="porcentaje_patronal" :label="__('% patronal (informativo)')" type="number" step="0.0001" required />
        <flux:input wire:model="tope_sbc_umas" :label="__('Tope de SBC (en veces UMA)')" type="number" required />
        <flux:textarea wire:model="notas" :label="__('Notas')" />

        <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>
    </form>
</section>
