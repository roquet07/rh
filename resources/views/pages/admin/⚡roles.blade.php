<?php

use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new #[Title('Roles')] class extends Component {
    private const ROL_PROTEGIDO = 'Administrador';

    public ?int $editingId = null;

    public string $name = '';

    /** @var array<int, string> */
    public array $permisosSeleccionados = [];

    /**
     * @return array<int, Role>
     */
    #[Computed]
    public function roles()
    {
        return Role::query()->withCount(['permissions', 'users'])->orderBy('name')->get();
    }

    /**
     * Catálogo de permisos existentes agrupado por módulo (lo que antecede al primer punto,
     * ej. "empleados" en "empleados.ver"). No se crean permisos nuevos aquí: solo se asignan
     * los que ya protegen rutas/acciones del sistema.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Permission>>
     */
    #[Computed]
    public function permisosPorModulo()
    {
        return Permission::query()->orderBy('name')->get()->groupBy(fn (Permission $permiso) => Str::before($permiso->name, '.'));
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'permisosSeleccionados');
        $this->resetValidation();

        Flux::modal('rol-form')->show();
    }

    public function edit(Role $rol): void
    {
        $this->editingId = $rol->id;
        $this->name = $rol->name;
        $this->permisosSeleccionados = $rol->permissions->pluck('name')->all();
        $this->resetValidation();

        Flux::modal('rol-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->editingId)],
        ]);

        if ($this->editingId !== null) {
            $actual = Role::query()->findOrFail($this->editingId);
            abort_if($actual->name === self::ROL_PROTEGIDO && $validated['name'] !== self::ROL_PROTEGIDO, 403, __('El rol :rol no se puede renombrar.', ['rol' => self::ROL_PROTEGIDO]));
        }

        $rol = Role::query()->updateOrCreate(['id' => $this->editingId], $validated);

        // El rol protegido siempre conserva todos los permisos: la app no tiene un bypass tipo
        // Gate::before, así que desmarcar permisos aquí podría dejar al sistema sin nadie que
        // pueda volver a entrar a esta misma pantalla a corregirlo.
        $rol->syncPermissions(
            $rol->name === self::ROL_PROTEGIDO ? Permission::all() : $this->permisosSeleccionados
        );

        Flux::modal('rol-form')->close();
        Flux::toast(variant: 'success', text: __('Rol guardado.'));

        unset($this->roles);
    }

    public function delete(Role $rol): void
    {
        abort_if($rol->name === self::ROL_PROTEGIDO, 403, __('El rol :rol no se puede eliminar.', ['rol' => self::ROL_PROTEGIDO]));

        $rol->delete();

        Flux::toast(variant: 'success', text: __('Rol eliminado.'));

        unset($this->roles);
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Roles') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Roles del sistema y los permisos que otorga cada uno.') }}</p>
        </div>

        <x-rh.button variant="primary" wire:click="create">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"></path></svg>
            {{ __('Nuevo rol') }}
        </x-rh.button>
    </div>

    <x-rh.card :padded="false">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr>
                    <x-rh.th>{{ __('Rol') }}</x-rh.th>
                    <x-rh.th>{{ __('Permisos') }}</x-rh.th>
                    <x-rh.th>{{ __('Usuarios') }}</x-rh.th>
                    <x-rh.th sr-only>{{ __('Acciones') }}</x-rh.th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->roles as $rol)
                    <tr class="hover:bg-surface-sunken" wire:key="rol-{{ $rol->id }}">
                        <x-rh.td class="font-medium">{{ $rol->name }}</x-rh.td>
                        <x-rh.td class="tabular-nums text-ink-muted">{{ $rol->permissions_count }}</x-rh.td>
                        <x-rh.td class="tabular-nums text-ink-muted">{{ $rol->users_count }}</x-rh.td>
                        <x-rh.td class="text-right">
                            <div class="flex justify-end gap-1">
                                <button type="button" wire:click="edit({{ $rol->id }})" aria-label="{{ __('Editar :nombre', ['nombre' => $rol->name]) }}" class="flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-ink cursor-pointer">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"></path></svg>
                                </button>
                                @if ($rol->name !== 'Administrador')
                                    <button type="button" wire:click="delete({{ $rol->id }})" wire:confirm="{{ __('¿Eliminar este rol? Los usuarios que lo tengan se quedarán sin rol.') }}" aria-label="{{ __('Eliminar :nombre', ['nombre' => $rol->name]) }}" class="flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-danger cursor-pointer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path></svg>
                                    </button>
                                @endif
                            </div>
                        </x-rh.td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-rh.empty-state
                                title="{{ __('Sin roles') }}"
                                description="{{ __('Aún no se han registrado roles.') }}"
                                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"></path></svg>'
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-rh.card>

    <flux:modal name="rol-form" class="max-w-2xl">
        <form wire:submit="save" class="flex flex-col gap-6">
            <h2 class="m-0 text-lg font-semibold">{{ $editingId ? __('Editar rol') : __('Nuevo rol') }}</h2>

            <x-rh.text-field wire:model="name" name="name" label="{{ __('Nombre del rol') }}" required :error="$errors->first('name')" :disabled="$name === 'Administrador'" />

            <div class="flex flex-col gap-3">
                <span class="text-sm font-medium text-ink">{{ __('Permisos') }}</span>

                @if ($name === 'Administrador')
                    <p class="m-0 text-sm text-ink-muted">{{ __('El rol Administrador siempre tiene todos los permisos del sistema.') }}</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-96 overflow-y-auto pr-1">
                        @foreach ($this->permisosPorModulo as $modulo => $permisos)
                            <div class="rounded-md border border-line p-3">
                                <p class="m-0 mb-2 text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ str_replace('-', ' ', $modulo) }}</p>
                                <div class="flex flex-col gap-1.5">
                                    @foreach ($permisos as $permiso)
                                        <label class="flex items-center gap-2 text-sm text-ink">
                                            <input type="checkbox" wire:model="permisosSeleccionados" value="{{ $permiso->name }}" class="h-4 w-4 accent-brand">
                                            {{ $permiso->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
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
