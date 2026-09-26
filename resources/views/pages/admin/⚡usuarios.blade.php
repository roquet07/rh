<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Title('Usuarios')] class extends Component {
    use PasswordValidationRules, ProfileValidationRules;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $rol = '';

    /**
     * @return array<int, User>
     */
    #[Computed]
    public function usuarios()
    {
        return User::query()->with('roles')->orderBy('name')->get();
    }

    /**
     * @return array<int, Role>
     */
    #[Computed]
    public function roles()
    {
        return Role::query()->orderBy('name')->get();
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'email', 'password', 'password_confirmation', 'rol');
        $this->resetValidation();

        Flux::modal('usuario-form')->show();
    }

    public function edit(User $usuario): void
    {
        $this->editingId = $usuario->id;
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->rol = $usuario->roles->first()?->name ?? '';
        $this->resetValidation();

        Flux::modal('usuario-form')->show();
    }

    public function save(): void
    {
        $esNuevo = $this->editingId === null;

        $validated = $this->validate([
            'name' => $this->nameRules(),
            'email' => $this->emailRules($this->editingId),
            'password' => $esNuevo ? $this->passwordRules() : ['nullable', 'string', Password::default(), 'confirmed'],
            'rol' => ['nullable', 'string', Rule::exists('roles', 'name')],
        ]);

        if ($esNuevo) {
            $usuario = new User;
            $usuario->password = $validated['password'];
        } else {
            $usuario = User::query()->findOrFail($this->editingId);

            if (filled($validated['password'])) {
                $usuario->password = $validated['password'];
            }
        }

        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];
        $usuario->email_verified_at ??= now();
        $usuario->save();

        $usuario->syncRoles(filled($validated['rol']) ? [$validated['rol']] : []);

        Flux::modal('usuario-form')->close();
        Flux::toast(variant: 'success', text: __('Usuario guardado.'));

        unset($this->usuarios);
    }

    public function delete(User $usuario): void
    {
        abort_if($usuario->id === Auth::id(), 403, __('No puedes eliminar tu propia cuenta.'));

        $usuario->delete();

        Flux::toast(variant: 'success', text: __('Usuario eliminado.'));

        unset($this->usuarios);
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Usuarios') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Cuentas del sistema y el rol asignado a cada una.') }}</p>
        </div>

        <x-rh.button variant="primary" wire:click="create">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"></path></svg>
            {{ __('Nuevo usuario') }}
        </x-rh.button>
    </div>

    <x-rh.card :padded="false">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr>
                    <x-rh.th>{{ __('Usuario') }}</x-rh.th>
                    <x-rh.th>{{ __('Correo') }}</x-rh.th>
                    <x-rh.th>{{ __('Rol') }}</x-rh.th>
                    <x-rh.th sr-only>{{ __('Acciones') }}</x-rh.th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->usuarios as $usuario)
                    <tr class="hover:bg-surface-sunken" wire:key="usuario-{{ $usuario->id }}">
                        <x-rh.td>
                            <div class="flex items-center gap-3">
                                <x-rh.avatar :name="$usuario->name" :photo-url="$usuario->avatar_path ? route('usuarios.foto', $usuario) : null" />
                                <span class="font-medium">{{ $usuario->name }}</span>
                            </div>
                        </x-rh.td>
                        <x-rh.td class="text-ink-muted">{{ $usuario->email }}</x-rh.td>
                        <x-rh.td>
                            @if ($usuario->roles->first())
                                <x-rh.badge tone="info" :label="$usuario->roles->first()->name" />
                            @else
                                <span class="text-ink-muted">{{ __('Sin rol') }}</span>
                            @endif
                        </x-rh.td>
                        <x-rh.td class="text-right">
                            <div class="flex justify-end gap-1">
                                <button type="button" wire:click="edit({{ $usuario->id }})" aria-label="{{ __('Editar :nombre', ['nombre' => $usuario->name]) }}" class="flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-ink cursor-pointer">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"></path></svg>
                                </button>
                                @if ($usuario->id !== Auth::id())
                                    <button type="button" wire:click="delete({{ $usuario->id }})" wire:confirm="{{ __('¿Eliminar este usuario?') }}" aria-label="{{ __('Eliminar :nombre', ['nombre' => $usuario->name]) }}" class="flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-danger cursor-pointer">
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
                                title="{{ __('Sin usuarios') }}"
                                description="{{ __('Aún no se han registrado usuarios.') }}"
                                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path></svg>'
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-rh.card>

    <flux:modal name="usuario-form" class="max-w-lg">
        <form wire:submit="save" class="flex flex-col gap-6">
            <h2 class="m-0 text-lg font-semibold">{{ $editingId ? __('Editar usuario') : __('Nuevo usuario') }}</h2>

            <x-rh.text-field wire:model="name" name="name" label="{{ __('Nombre') }}" required :error="$errors->first('name')" />
            <x-rh.text-field wire:model="email" name="email" type="email" label="{{ __('Correo') }}" required :error="$errors->first('email')" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-rh.text-field wire:model="password" name="password" type="password" label="{{ $editingId ? __('Nueva contraseña (opcional)') : __('Contraseña') }}" :required="! $editingId" :error="$errors->first('password')" />
                <x-rh.text-field wire:model="password_confirmation" name="password_confirmation" type="password" label="{{ __('Confirmar contraseña') }}" :required="! $editingId" />
            </div>

            <x-rh.select wire:model="rol" name="rol" label="{{ __('Rol') }}" :placeholder="null">
                <option value="">{{ __('Sin rol') }}</option>
                @foreach ($this->roles as $r)
                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                @endforeach
            </x-rh.select>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <x-rh.button type="button" variant="secondary">{{ __('Cancelar') }}</x-rh.button>
                </flux:modal.close>

                <x-rh.button type="submit" variant="primary">{{ __('Guardar') }}</x-rh.button>
            </div>
        </form>
    </flux:modal>
</section>
