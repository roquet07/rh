<x-layouts::auth :title="__('Olvidé mi contraseña')">
    <div class="flex flex-col gap-2">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('¿Olvidaste tu contraseña?') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ __('Ingresa tu correo para recibir un enlace de restablecimiento.') }}</p>
    </div>

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
        @csrf

        <x-rh.text-field
            name="email"
            label="{{ __('Correo electrónico') }}"
            type="email"
            required
            autofocus
            placeholder="tu@empresa.mx"
            :error="$errors->first('email')"
        />

        <x-rh.button type="submit" variant="primary" size="lg" class="w-full" data-test="email-password-reset-link-button">
            {{ __('Enviar enlace de restablecimiento') }}
        </x-rh.button>
    </form>

    <p class="m-0 text-center text-sm text-ink-muted">
        {{ __('¿Ya lo recordaste?') }}
        <a href="{{ route('login') }}" wire:navigate class="font-medium text-brand hover:text-brand-hover hover:underline">{{ __('Inicia sesión') }}</a>
    </p>
</x-layouts::auth>
