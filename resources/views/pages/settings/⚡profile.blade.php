<?php

use App\Actions\Users\ActualizarAvatarUsuario;
use App\Actions\Users\EliminarAvatarUsuario;
use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';
    public string $email = '';

    public ?UploadedFile $avatar = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }

    public function subirAvatar(ActualizarAvatarUsuario $accion): void
    {
        $this->validate(['avatar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120']]);

        $accion(Auth::user(), $this->avatar);

        $this->reset('avatar');

        Flux::toast(variant: 'success', text: __('Foto de perfil actualizada.'));
    }

    public function eliminarAvatar(EliminarAvatarUsuario $accion): void
    {
        $accion(Auth::user());

        Flux::toast(variant: 'success', text: __('Foto de perfil eliminada.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <div class="my-6 flex items-center gap-4">
            <x-rh.avatar :name="Auth::user()->name" :photo-url="Auth::user()->avatar_path ? route('usuarios.foto', Auth::user()) : null" size="lg" />

            <div class="flex flex-col gap-1.5">
                <input type="file" wire:model="avatar" id="avatar-usuario" class="hidden">
                <div class="flex items-center gap-1.5">
                    <label for="avatar-usuario" class="text-sm font-medium text-brand hover:underline cursor-pointer">{{ __('Cambiar foto') }}</label>
                    @if (Auth::user()->avatar_path)
                        <span class="text-sm text-ink-muted">·</span>
                        <button type="button" wire:click="eliminarAvatar" wire:confirm="{{ __('¿Quitar tu foto de perfil?') }}" class="text-sm font-medium text-danger hover:underline cursor-pointer">{{ __('Quitar') }}</button>
                    @endif
                </div>
                @if ($avatar)
                    <flux:button size="sm" wire:click="subirAvatar" class="self-start">{{ __('Confirmar') }}</flux:button>
                @endif
                @error('avatar')
                    <p class="text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
