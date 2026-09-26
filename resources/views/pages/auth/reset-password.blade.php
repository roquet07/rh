<x-layouts::auth :title="__('Restablecer contraseña')">
    <div class="flex flex-col gap-2">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Restablecer contraseña') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ __('Ingresa tu nueva contraseña.') }}</p>
    </div>

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">

        <x-rh.text-field
            name="email"
            label="{{ __('Correo electrónico') }}"
            type="email"
            value="{{ request('email') }}"
            required
            autocomplete="email"
            :error="$errors->first('email')"
        />

        <x-rh.password-field name="password" id="password" label="{{ __('Contraseña') }}" strength required autocomplete="new-password" placeholder="{{ __('Nueva contraseña') }}" :error="$errors->first('password')" />

        <x-rh.password-field name="password_confirmation" id="password_confirmation" label="{{ __('Confirmar contraseña') }}" required autocomplete="new-password" placeholder="{{ __('Repite la contraseña') }}" />

        <x-rh.button type="submit" variant="primary" size="lg" class="w-full" data-test="reset-password-button">
            {{ __('Restablecer contraseña') }}
        </x-rh.button>
    </form>
</x-layouts::auth>
