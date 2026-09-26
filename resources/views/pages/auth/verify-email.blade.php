<x-layouts::auth :title="__('Verificación de correo')">
    <div class="flex flex-col gap-2 text-center">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Verifica tu correo') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ __('Confirma tu dirección de correo dando clic en el enlace que te enviamos.') }}</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <p class="text-center text-sm font-medium text-success">
            {{ __('Te enviamos un nuevo enlace de verificación al correo que registraste.') }}
        </p>
    @endif

    <div class="flex flex-col items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full">
            @csrf
            <x-rh.button type="submit" variant="primary" size="lg" class="w-full">
                {{ __('Reenviar correo de verificación') }}
            </x-rh.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-rh.button type="submit" variant="ghost" data-test="logout-button">
                {{ __('Cerrar sesión') }}
            </x-rh.button>
        </form>
    </div>
</x-layouts::auth>
