<?php

use App\Models\IsrBracket;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tabla ISR')] class extends Component {
    public ?int $editingId = null;

    public int $ejercicio_fiscal;

    public string $periodicidad = 'quincenal';

    public string $limite_inferior = '';

    public string $limite_superior = '';

    public string $cuota_fija = '';

    public string $porcentaje_excedente = '';

    public function mount(): void
    {
        $this->ejercicio_fiscal = now()->year;
    }

    #[Computed]
    public function brackets()
    {
        return IsrBracket::query()
            ->where('ejercicio_fiscal', $this->ejercicio_fiscal)
            ->where('periodicidad', $this->periodicidad)
            ->orderBy('limite_inferior')
            ->get();
    }

    public function edit(IsrBracket $bracket): void
    {
        $this->editingId = $bracket->id;
        $this->limite_inferior = (string) $bracket->limite_inferior;
        $this->limite_superior = (string) $bracket->limite_superior;
        $this->cuota_fija = (string) $bracket->cuota_fija;
        $this->porcentaje_excedente = (string) $bracket->porcentaje_excedente;

        Flux::modal('isr-form')->show();
    }

    public function create(): void
    {
        $this->reset('editingId', 'limite_inferior', 'limite_superior', 'cuota_fija', 'porcentaje_excedente');

        Flux::modal('isr-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'limite_inferior' => ['required', 'numeric', 'min:0'],
            'limite_superior' => ['nullable', 'numeric', 'gt:limite_inferior'],
            'cuota_fija' => ['required', 'numeric', 'min:0'],
            'porcentaje_excedente' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        IsrBracket::query()->updateOrCreate(
            ['id' => $this->editingId],
            $validated + ['ejercicio_fiscal' => $this->ejercicio_fiscal, 'periodicidad' => $this->periodicidad],
        );

        Flux::modal('isr-form')->close();
        Flux::toast(variant: 'success', text: __('Rango ISR guardado.'));

        unset($this->brackets);
    }

    public function delete(IsrBracket $bracket): void
    {
        $bracket->delete();

        Flux::toast(variant: 'success', text: __('Rango eliminado.'));

        unset($this->brackets);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl">{{ __('Configuración fiscal — ISR') }}</flux:heading>
    <flux:subheading>{{ __('Tarifa del Art. 96 LISR usada para calcular la retención de ISR en nómina.') }}</flux:subheading>

    <flux:callout class="mt-4" variant="warning" icon="exclamation-triangle">
        {{ __('Estos valores deben mantenerse actualizados con la tarifa oficial vigente publicada por el SAT para el ejercicio fiscal en curso.') }}
    </flux:callout>

    <div class="mt-6 flex items-center justify-between">
        <div class="flex gap-4">
            <flux:input wire:model.live="ejercicio_fiscal" :label="__('Ejercicio fiscal')" type="number" class="max-w-xs" />

            <flux:select wire:model.live="periodicidad" :label="__('Periodicidad')" class="max-w-xs">
                <flux:select.option value="semanal">{{ __('Semanal') }}</flux:select.option>
                <flux:select.option value="decenal">{{ __('Decenal') }}</flux:select.option>
                <flux:select.option value="catorcenal">{{ __('Catorcenal') }}</flux:select.option>
                <flux:select.option value="quincenal">{{ __('Quincenal') }}</flux:select.option>
                <flux:select.option value="mensual">{{ __('Mensual') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">{{ __('Nuevo rango') }}</flux:button>
    </div>

    <flux:table class="mt-6">
        <flux:table.columns>
            <flux:table.column>{{ __('Límite inferior') }}</flux:table.column>
            <flux:table.column>{{ __('Límite superior') }}</flux:table.column>
            <flux:table.column>{{ __('Cuota fija') }}</flux:table.column>
            <flux:table.column>{{ __('% Excedente') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->brackets as $bracket)
                <flux:table.row wire:key="bracket-{{ $bracket->id }}">
                    <flux:table.cell>${{ number_format((float) $bracket->limite_inferior, 2) }}</flux:table.cell>
                    <flux:table.cell>{{ $bracket->limite_superior ? '$'.number_format((float) $bracket->limite_superior, 2) : __('en adelante') }}</flux:table.cell>
                    <flux:table.cell>${{ number_format((float) $bracket->cuota_fija, 2) }}</flux:table.cell>
                    <flux:table.cell>{{ number_format((float) $bracket->porcentaje_excedente, 2) }}%</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $bracket->id }})" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $bracket->id }})" wire:confirm="{{ __('¿Eliminar este rango?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">{{ __('Sin rangos para este ejercicio/periodicidad.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="isr-form" class="max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? __('Editar rango') : __('Nuevo rango') }}</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model="limite_inferior" :label="__('Límite inferior')" type="number" step="0.01" required />
                <flux:input wire:model="limite_superior" :label="__('Límite superior (vacío = sin límite)')" type="number" step="0.01" />
                <flux:input wire:model="cuota_fija" :label="__('Cuota fija')" type="number" step="0.01" required />
                <flux:input wire:model="porcentaje_excedente" :label="__('% sobre excedente')" type="number" step="0.01" required />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="filled">{{ __('Cancelar') }}</flux:button></flux:modal.close>
                <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
