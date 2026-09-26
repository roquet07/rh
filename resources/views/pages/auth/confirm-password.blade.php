<x-layouts::auth :title="__('Confirmar contraseña')">
    <div class="flex flex-col gap-2">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Confirma tu contraseña') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ __('Esta es un área segura de la aplicación. Confirma tu contraseña antes de continuar.') }}</p>
    </div>

    <x-auth-session-status class="text-center" :status="session('status')" />

    <x-passkey-verify
        options-route="passkey.confirm-options"
        submit-route="passkey.confirm"
        :label="__('Confirmar con passkey')"
        :loading-label="__('Confirmando...')"
        :separator="__('O confirma con tu contraseña')"
    />

    <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-4">
        @csrf

        <x-rh.password-field name="password" id="password" label="{{ __('Contraseña') }}" required autocomplete="current-password" placeholder="{{ __('Tu contraseña') }}" :error="$errors->first('password')" />

        <x-rh.button type="submit" variant="primary" size="lg" class="w-full" data-test="confirm-password-button">
            {{ __('Confirmar') }}
        </x-rh.button>
    </form>
</x-layouts::auth>
