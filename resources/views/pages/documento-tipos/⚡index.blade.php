<?php

use App\Models\DocumentoTipo;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tipos de documento')] class extends Component {
    public ?int $editingId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public bool $obligatorio = true;

    public bool $activo = true;

    public string $orden = '0';

    /**
     * @return array<int, DocumentoTipo>
     */
    #[Computed]
    public function documentoTipos()
    {
        return DocumentoTipo::query()->orderBy('orden')->orderBy('nombre')->get();
    }

    public function create(): void
    {
        $this->reset('editingId', 'nombre', 'descripcion', 'obligatorio', 'activo', 'orden');
        $this->obligatorio = true;
        $this->activo = true;
        $this->orden = '0';
        $this->resetValidation();

        Flux::modal('documento-tipo-form')->show();
    }

    public function edit(DocumentoTipo $documentoTipo): void
    {
        $this->editingId = $documentoTipo->id;
        $this->nombre = $documentoTipo->nombre;
        $this->descripcion = (string) $documentoTipo->descripcion;
        $this->obligatorio = $documentoTipo->obligatorio;
        $this->activo = $documentoTipo->activo;
        $this->orden = (string) $documentoTipo->orden;
        $this->resetValidation();

        Flux::modal('documento-tipo-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'obligatorio' => ['boolean'],
            'activo' => ['boolean'],
            'orden' => ['required', 'integer', 'min:0'],
        ]);

        DocumentoTipo::query()->updateOrCreate(['id' => $this->editingId], $validated);

        Flux::modal('documento-tipo-form')->close();
        Flux::toast(variant: 'success', text: __('Tipo de documento guardado.'));

        unset($this->documentoTipos);
    }

    public function delete(DocumentoTipo $documentoTipo): void
    {
        try {
            $documentoTipo->delete();

            Flux::toast(variant: 'success', text: __('Tipo de documento eliminado.'));
        } catch (QueryException) {
            Flux::toast(variant: 'danger', text: __('No se puede eliminar: ya tiene documentos capturados. Desactívalo en su lugar.'));
        }

        unset($this->documentoTipos);
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Tipos de documento') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Catálogo de documentos que RH debe capturar para cada empleado.') }}</p>
        </div>

        @can('documento-tipos.gestionar')
            <x-rh.button variant="primary" wire:click="create">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                {{ __('Nuevo tipo') }}
            </x-rh.button>
        @endcan
    </div>

    <x-rh.card :padded="false">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr>
                    <x-rh.th>{{ __('Nombre') }}</x-rh.th>
                    <x-rh.th>{{ __('Descripción') }}</x-rh.th>
                    <x-rh.th>{{ __('Obligatorio') }}</x-rh.th>
                    <x-rh.th>{{ __('Activo') }}</x-rh.th>
                    <x-rh.th>{{ __('Orden') }}</x-rh.th>
                    @can('documento-tipos.gestionar')
                        <x-rh.th sr-only>{{ __('Acciones') }}</x-rh.th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse ($this->documentoTipos as $documentoTipo)
                    <tr class="hover:bg-surface-sunken" wire:key="documento-tipo-{{ $documentoTipo->id }}">
                        <x-rh.td class="font-medium">{{ $documentoTipo->nombre }}</x-rh.td>
                        <x-rh.td class="text-ink-muted">{{ $documentoTipo->descripcion ?: '—' }}</x-rh.td>
                        <x-rh.td>
                            <x-rh.badge :tone="$documentoTipo->obligatorio ? 'warning' : 'neutral'" :label="$documentoTipo->obligatorio ? __('Obligatorio') : __('Opcional')" />
                        </x-rh.td>
                        <x-rh.td>
                            <x-rh.badge :tone="$documentoTipo->activo ? 'success' : 'neutral'" :label="$documentoTipo->activo ? __('Activo') : __('Inactivo')" />
                        </x-rh.td>
                        <x-rh.td class="tabular-nums">{{ $documentoTipo->orden }}</x-rh.td>
                        @can('documento-tipos.gestionar')
                            <x-rh.td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button" wire:click="edit({{ $documentoTipo->id }})" aria-label="{{ __('Editar :nombre', ['nombre' => $documentoTipo->nombre]) }}" class="flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-ink cursor-pointer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"></path></svg>
                                    </button>
                                    <button type="button" wire:click="delete({{ $documentoTipo->id }})" wire:confirm="{{ __('¿Eliminar este tipo de documento?') }}" aria-label="{{ __('Eliminar :nombre', ['nombre' => $documentoTipo->nombre]) }}" class="flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-danger cursor-pointer">
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
                                title="{{ __('Sin tipos de documento') }}"
                                description="{{ __('Aún no se han registrado tipos de documento.') }}"
                                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path></svg>'
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-rh.card>

    <flux:modal name="documento-tipo-form" class="max-w-lg">
        <form wire:submit="save" class="flex flex-col gap-6">
            <h2 class="m-0 text-lg font-semibold">{{ $editingId ? __('Editar tipo de documento') : __('Nuevo tipo de documento') }}</h2>

            <x-rh.text-field wire:model="nombre" name="nombre" label="{{ __('Nombre') }}" required :error="$errors->first('nombre')" />

            <div class="flex flex-col gap-1.5">
                <label for="descripcion" class="text-sm font-medium text-ink">{{ __('Descripción') }}</label>
                <textarea wire:model="descripcion" id="descripcion" name="descripcion" rows="3" class="w-full rounded-md border border-line-strong bg-surface-raised px-3 py-2 text-sm text-ink focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus"></textarea>
            </div>

            <x-rh.text-field wire:model="orden" name="orden" type="number" label="{{ __('Orden') }}" required :error="$errors->first('orden')" />

            <div class="flex flex-col gap-3">
                <label class="flex items-center gap-2 text-sm font-medium text-ink">
                    <input type="checkbox" wire:model="obligatorio" class="h-4 w-4 accent-brand">
                    {{ __('Obligatorio') }}
                </label>

                <label class="flex items-center gap-2 text-sm font-medium text-ink">
                    <input type="checkbox" wire:model="activo" class="h-4 w-4 accent-brand">
                    {{ __('Activo') }}
                </label>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <x-rh.button type="button" variant="secondary">{{ __('Cancelar') }}</x-rh.button>
                </flux:modal.close>

                <x-rh.button type="submit" variant="primary">{{ __('Guardar') }}</x-rh.button>
            </div>
        </form>
    </flux:modal>
</section>
