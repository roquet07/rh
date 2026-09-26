<?php

use App\Models\Departamento;
use App\Models\Puesto;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Puestos')] class extends Component {
    public ?int $editingId = null;

    public ?int $departamento_id = null;

    public string $nombre = '';

    public string $descripcion = '';

    public string $salario_min = '';

    public string $salario_max = '';

    public string $porcentaje_comision = '0';

    public string $jornada = 'diurna';

    public bool $activo = true;

    /**
     * @return array<int, Puesto>
     */
    #[Computed]
    public function puestos()
    {
        return Puesto::query()->with('departamento')->orderBy('nombre')->get();
    }

    /**
     * @return array<int, Departamento>
     */
    #[Computed]
    public function departamentos()
    {
        return Departamento::query()->where('activo', true)->orderBy('nombre')->get();
    }

    public function create(): void
    {
        $this->reset('editingId', 'departamento_id', 'nombre', 'descripcion', 'salario_min', 'salario_max', 'porcentaje_comision', 'jornada', 'activo');
        $this->porcentaje_comision = '0';
        $this->jornada = 'diurna';
        $this->activo = true;
        $this->resetValidation();

        Flux::modal('puesto-form')->show();
    }

    public function edit(Puesto $puesto): void
    {
        $this->editingId = $puesto->id;
        $this->departamento_id = $puesto->departamento_id;
        $this->nombre = $puesto->nombre;
        $this->descripcion = (string) $puesto->descripcion;
        $this->salario_min = (string) $puesto->salario_min;
        $this->salario_max = (string) $puesto->salario_max;
        $this->porcentaje_comision = (string) $puesto->porcentaje_comision;
        $this->jornada = $puesto->jornada;
        $this->activo = $puesto->activo;
        $this->resetValidation();

        Flux::modal('puesto-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'salario_min' => ['required', 'numeric', 'min:0'],
            'salario_max' => ['required', 'numeric', 'gte:salario_min'],
            'porcentaje_comision' => ['required', 'numeric', 'min:0', 'max:100'],
            'jornada' => ['required', Rule::in(['diurna', 'nocturna', 'mixta'])],
            'activo' => ['boolean'],
        ]);

        Puesto::query()->updateOrCreate(['id' => $this->editingId], $validated);

        Flux::modal('puesto-form')->close();
        Flux::toast(variant: 'success', text: __('Puesto guardado.'));

        unset($this->puestos);
    }

    public function delete(Puesto $puesto): void
    {
        $puesto->delete();

        Flux::toast(variant: 'success', text: __('Puesto eliminado.'));

        unset($this->puestos);
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Puestos') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Catálogo de puestos, rangos salariales y comisión por defecto.') }}</p>
        </div>

        @can('puestos.gestionar')
            <x-rh.button variant="primary" wire:click="create">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                {{ __('Nuevo puesto') }}
            </x-rh.button>
        @endcan
    </div>

    <x-rh.card :padded="false">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr>
                    <x-rh.th>{{ __('Nombre') }}</x-rh.th>
                    <x-rh.th>{{ __('Departamento') }}</x-rh.th>
                    <x-rh.th>{{ __('Rango salarial (diario)') }}</x-rh.th>
                    <x-rh.th>{{ __('% Comisión') }}</x-rh.th>
                    <x-rh.th>{{ __('Jornada') }}</x-rh.th>
                    @can('puestos.gestionar')
                        <x-rh.th sr-only>{{ __('Acciones') }}</x-rh.th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse ($this->puestos as $puesto)
                    <tr class="hover:bg-surface-sunken" wire:key="puesto-{{ $puesto->id }}">
                        <x-rh.td class="font-medium">{{ $puesto->nombre }}</x-rh.td>
                        <x-rh.td class="text-ink-muted">{{ $puesto->departamento->nombre }}</x-rh.td>
                        <x-rh.td class="tabular-nums">${{ number_format((float) $puesto->salario_min, 2) }} – ${{ number_format((float) $puesto->salario_max, 2) }}</x-rh.td>
                        <x-rh.td class="tabular-nums">{{ number_format((float) $puesto->porcentaje_comision, 2) }}%</x-rh.td>
                        <x-rh.td class="capitalize">{{ $puesto->jornada }}</x-rh.td>
                        @can('puestos.gestionar')
                            <x-rh.td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button" wire:click="edit({{ $puesto->id }})" aria-label="{{ __('Editar :nombre', ['nombre' => $puesto->nombre]) }}" class="flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-ink cursor-pointer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"></path></svg>
                                    </button>
                                    <button type="button" wire:click="delete({{ $puesto->id }})" wire:confirm="{{ __('¿Eliminar este puesto?') }}" aria-label="{{ __('Eliminar :nombre', ['nombre' => $puesto->nombre]) }}" class="flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-danger cursor-pointer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path></svg>
                                    </button>
                                </div>
                            </x-rh.td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-rh.empty-state
                                title="{{ __('Sin puestos') }}"
                                description="{{ __('Aún no se han registrado puestos.') }}"
                                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8.25h18v11.25H3zM8.25 8.25V6a1.5 1.5 0 0 1 1.5-1.5h4.5a1.5 1.5 0 0 1 1.5 1.5v2.25M3 13.5h18"></path></svg>'
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-rh.card>

    <flux:modal name="puesto-form" class="max-w-lg">
        <form wire:submit="save" class="flex flex-col gap-6">
            <h2 class="m-0 text-lg font-semibold">{{ $editingId ? __('Editar puesto') : __('Nuevo puesto') }}</h2>

            <x-rh.select wire:model="departamento_id" name="departamento_id" label="{{ __('Departamento') }}" required :error="$errors->first('departamento_id')">
                @foreach ($this->departamentos as $departamento)
                    <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                @endforeach
            </x-rh.select>

            <x-rh.text-field wire:model="nombre" name="nombre" label="{{ __('Nombre del puesto') }}" required :error="$errors->first('nombre')" />

            <div class="flex flex-col gap-1.5">
                <label for="descripcion" class="text-sm font-medium text-ink">{{ __('Descripción') }}</label>
                <textarea wire:model="descripcion" id="descripcion" name="descripcion" rows="3" class="w-full rounded-md border border-line-strong bg-surface-raised px-3 py-2 text-sm text-ink focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-rh.text-field wire:model="salario_min" name="salario_min" type="number" step="0.01" label="{{ __('Salario mínimo (diario)') }}" required :error="$errors->first('salario_min')" />
                <x-rh.text-field wire:model="salario_max" name="salario_max" type="number" step="0.01" label="{{ __('Salario máximo (diario)') }}" required :error="$errors->first('salario_max')" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-rh.text-field wire:model="porcentaje_comision" name="porcentaje_comision" type="number" step="0.01" label="{{ __('% Comisión por defecto') }}" required :error="$errors->first('porcentaje_comision')" />

                <x-rh.select wire:model="jornada" name="jornada" label="{{ __('Jornada') }}" required>
                    <option value="diurna">{{ __('Diurna (8h)') }}</option>
                    <option value="nocturna">{{ __('Nocturna (7h)') }}</option>
                    <option value="mixta">{{ __('Mixta (7.5h)') }}</option>
                </x-rh.select>
            </div>

            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                <input type="checkbox" wire:model="activo" class="h-4 w-4 accent-brand">
                {{ __('Activo') }}
            </label>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <x-rh.button type="button" variant="secondary">{{ __('Cancelar') }}</x-rh.button>
                </flux:modal.close>

                <x-rh.button type="submit" variant="primary">{{ __('Guardar') }}</x-rh.button>
            </div>
        </form>
    </flux:modal>
</section>
