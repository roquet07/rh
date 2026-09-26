<x-layouts::auth :title="__('Iniciar sesión')">
    <div class="flex flex-col gap-2">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Inicia sesión') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ __('Accede a los expedientes, contratos y nómina de tu empresa.') }}</p>
    </div>

    <x-auth-session-status class="text-center" :status="session('status')" />

    <x-passkey-verify />

    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
        @csrf

        <x-rh.text-field
            name="email"
            label="{{ __('Correo electrónico') }}"
            type="email"
            value="{{ old('email') }}"
            required
            autofocus
            autocomplete="email"
            placeholder="tu@empresa.mx"
            :error="$errors->first('email')"
        />

        <div class="flex flex-col gap-1.5">
            <div class="flex justify-between">
                <label for="password" class="text-sm font-medium text-ink">{{ __('Contraseña') }}</label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-medium text-brand hover:text-brand-hover hover:underline">
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif
            </div>

            <x-rh.password-field name="password" id="password" required autocomplete="current-password" placeholder="{{ __('Tu contraseña') }}" :error="$errors->first('password')" />
        </div>

        <label class="flex items-center gap-2 text-sm text-ink">
            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 accent-brand">
            {{ __('Mantener la sesión iniciada') }}
        </label>

        <x-rh.button type="submit" variant="primary" size="lg" class="w-full" data-test="login-button">
            {{ __('Iniciar sesión') }}
        </x-rh.button>
    </form>

    <p class="m-0 text-center text-sm text-ink-muted">
        {{ __('¿Aún no tienes cuenta?') }}
        <a href="{{ route('register') }}" wire:navigate class="font-medium text-brand hover:text-brand-hover hover:underline">{{ __('Crea una cuenta') }}</a>
    </p>
</x-layouts::auth>
