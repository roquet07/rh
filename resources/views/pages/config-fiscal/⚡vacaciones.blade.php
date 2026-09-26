<?php

use App\Models\TablaVacacionesAntiguedad;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tabla de vacaciones por antigüedad')] class extends Component {
    public ?int $editingId = null;

    public string $dias_vacaciones = '';

    #[Computed]
    public function filas()
    {
        return TablaVacacionesAntiguedad::query()->orderBy('anios_antiguedad')->get();
    }

    public function edit(TablaVacacionesAntiguedad $fila): void
    {
        $this->editingId = $fila->id;
        $this->dias_vacaciones = (string) $fila->dias_vacaciones;

        Flux::modal('vacaciones-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'dias_vacaciones' => ['required', 'integer', 'min:1'],
        ]);

        TablaVacacionesAntiguedad::query()->whereKey($this->editingId)->update($validated);

        Flux::modal('vacaciones-form')->close();
        Flux::toast(variant: 'success', text: __('Días de vacaciones actualizados.'));

        unset($this->filas);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl">{{ __('Configuración fiscal — Vacaciones') }}</flux:heading>
    <flux:subheading>{{ __('Días de vacaciones por año de antigüedad, conforme al Art. 76 LFT (reforma 2023).') }}</flux:subheading>

    <flux:table class="mt-6">
        <flux:table.columns>
            <flux:table.column>{{ __('Años de antigüedad') }}</flux:table.column>
            <flux:table.column>{{ __('Días de vacaciones') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->filas as $fila)
                <flux:table.row wire:key="fila-{{ $fila->id }}">
                    <flux:table.cell>{{ $fila->anios_antiguedad }}</flux:table.cell>
                    <flux:table.cell>{{ $fila->dias_vacaciones }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $fila->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="vacaciones-form" class="max-w-sm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ __('Editar días de vacaciones') }}</flux:heading>

            <flux:input wire:model="dias_vacaciones" :label="__('Días de vacaciones')" type="number" required />

            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="filled">{{ __('Cancelar') }}</flux:button></flux:modal.close>
                <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
