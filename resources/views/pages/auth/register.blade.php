@php
    $errorsPaso1 = collect(['name', 'email', 'password', 'password_confirmation'])->some(fn ($campo) => $errors->has($campo));
    $pasoInicial = $errorsPaso1 ? 0 : 0;
@endphp

<x-layouts::auth :title="__('Crear cuenta')" eyebrow="{{ __('Prueba Talento RH') }}" headline="{{ __('Configura tu empresa y da de alta a tu equipo hoy.') }}" tagline="{{ __('Crea tu cuenta, registra tu empresa y empieza por los expedientes. Puedes invitar a más personas después.') }}">
    <div x-data="{ step: {{ $pasoInicial }} }">
        <div class="flex flex-col gap-2">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Crea tu cuenta') }}</h1>
            <p class="m-0 text-base text-ink-muted" x-text="step === 0 ? '{{ __('Empieza con tus datos de acceso.') }}' : '{{ __('Ahora, los datos de tu empresa.') }}'"></p>
        </div>

        <ol aria-label="{{ __('Pasos del registro') }}" class="list-none m-0 p-0 mt-6 grid grid-cols-2 gap-2">
            <li class="flex flex-col gap-2">
                <span class="h-1 rounded-full" :class="step >= 0 ? 'bg-brand' : 'bg-line'"></span>
                <span class="text-xs font-medium" :class="step >= 0 ? 'text-ink' : 'text-ink-muted'">{{ __('1. Tu cuenta') }}</span>
            </li>
            <li class="flex flex-col gap-2">
                <span class="h-1 rounded-full" :class="step >= 1 ? 'bg-brand' : 'bg-line'"></span>
                <span class="text-xs font-medium" :class="step >= 1 ? 'text-ink' : 'text-ink-muted'">{{ __('2. Tu empresa') }}</span>
            </li>
        </ol>

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 flex flex-col gap-4" x-data="{
            camposValidos() {
                const paso1 = this.$refs.paso1;
                const requeridos = paso1.querySelectorAll('[required]');
                for (const campo of requeridos) {
                    if (!campo.value.trim()) { campo.reportValidity(); return false; }
                }
                if (this.$refs.password.value !== this.$refs.passwordConfirmation.value) {
                    this.$refs.passwordConfirmation.setCustomValidity('{{ __('Las contraseñas no coinciden.') }}');
                    this.$refs.passwordConfirmation.reportValidity();
                    return false;
                }
                this.$refs.passwordConfirmation.setCustomValidity('');
                return true;
            },
            siguiente() {
                if (this.camposValidos()) { step = 1; }
            },
        }">
            @csrf

            <div x-show="step === 0" x-ref="paso1" class="flex flex-col gap-4">
                <x-rh.text-field
                    name="name"
                    label="{{ __('Nombre completo') }}"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="{{ __('Ej. Ana López Mireles') }}"
                    :error="$errors->first('name')"
                />

                <x-rh.text-field
                    name="email"
                    label="{{ __('Correo de trabajo') }}"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="tu@empresa.mx"
                    :error="$errors->first('email')"
                />

                <x-rh.password-field
                    name="password"
                    id="password"
                    x-ref="password"
                    label="{{ __('Contraseña') }}"
                    strength
                    required
                    autocomplete="new-password"
                    placeholder="{{ __('Crea una contraseña') }}"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    :error="$errors->first('password')"
                />

                <x-rh.password-field
                    name="password_confirmation"
                    id="password_confirmation"
                    x-ref="passwordConfirmation"
                    label="{{ __('Confirmar contraseña') }}"
                    required
                    autocomplete="new-password"
                    placeholder="{{ __('Repite la contraseña') }}"
                />

                <x-rh.button type="button" variant="primary" size="lg" class="w-full mt-2" @click="siguiente()">
                    {{ __('Continuar') }}
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"></path></svg>
                </x-rh.button>
            </div>

            <div x-show="step === 1" class="flex flex-col gap-4">
                <x-rh.text-field
                    name="empresa_nombre"
                    label="{{ __('Nombre de la empresa') }}"
                    value="{{ old('empresa_nombre') }}"
                    placeholder="{{ __('Ej. Comercializadora del Bajío') }}"
                    autocomplete="organization"
                    :error="$errors->first('empresa_nombre')"
                />

                <x-rh.text-field
                    name="empresa_rfc"
                    label="{{ __('RFC de la empresa') }}"
                    value="{{ old('empresa_rfc') }}"
                    mono
                    maxlength="12"
                    placeholder="XAX010101000"
                    hint="{{ __('12 caracteres para persona moral.') }}"
                    :error="$errors->first('empresa_rfc')"
                />

                <x-rh.select name="empresa_tamano" label="{{ __('Número de colaboradores') }}" :error="$errors->first('empresa_tamano')">
                    @foreach (['1 a 10', '11 a 50', '51 a 200', 'Más de 200'] as $rango)
                        <option value="{{ $rango }}" @selected(old('empresa_tamano') === $rango)>{{ $rango }}</option>
                    @endforeach
                </x-rh.select>

                <label class="flex items-start gap-2 text-sm">
                    <input type="checkbox" required class="h-4 w-4 mt-0.5 accent-brand">
                    <span>{{ __('Acepto los') }} <a href="#" class="text-brand hover:underline">{{ __('términos de uso') }}</a> {{ __('y el') }} <a href="#" class="text-brand hover:underline">{{ __('aviso de privacidad') }}</a>.</span>
                </label>

                <div class="flex gap-2 mt-2">
                    <x-rh.button type="button" variant="secondary" size="lg" @click="step = 0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 19.5 8.25 12l7.5-7.5"></path></svg>
                        {{ __('Atrás') }}
                    </x-rh.button>

                    <x-rh.button type="submit" variant="primary" size="lg" class="flex-1" data-test="register-user-button">
                        {{ __('Crear cuenta') }}
                    </x-rh.button>
                </div>
            </div>
        </form>

        <p class="m-0 mt-6 text-center text-sm text-ink-muted">
            {{ __('¿Ya tienes cuenta?') }}
            <a href="{{ route('login') }}" wire:navigate class="font-medium text-brand hover:text-brand-hover hover:underline">{{ __('Inicia sesión') }}</a>
        </p>
    </div>
</x-layouts::auth>
