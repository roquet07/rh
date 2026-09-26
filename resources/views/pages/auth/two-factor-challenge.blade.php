<x-layouts::auth :title="__('Verificación en dos pasos')">
    <div
        class="relative w-full h-auto"
        x-cloak
        x-data="{
            showRecoveryInput: @js($errors->has('recovery_code')),
            code: '',
            recovery_code: '',
            focusOtp() {
                this.$nextTick(() => this.$refs.otp?.querySelector('input')?.focus());
            },
            init() {
                if (! this.showRecoveryInput) {
                    this.focusOtp();
                }
            },
            toggleInput() {
                this.showRecoveryInput = !this.showRecoveryInput;

                this.code = '';
                this.recovery_code = '';

                $nextTick(() => {
                    this.showRecoveryInput
                        ? this.$refs.recovery_code?.focus()
                        : this.focusOtp();
                });
            },
        }"
    >
        <div x-show="!showRecoveryInput" class="flex flex-col gap-2 text-center">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Código de autenticación') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Ingresa el código provisto por tu aplicación de autenticación.') }}</p>
        </div>

        <div x-show="showRecoveryInput" class="flex flex-col gap-2 text-center">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Código de recuperación') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Confirma el acceso a tu cuenta ingresando uno de tus códigos de recuperación.') }}</p>
        </div>

        <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-6">
            @csrf

            <div class="space-y-5 text-center">
                <div x-show="!showRecoveryInput">
                    <div class="flex items-center justify-center my-5" x-ref="otp">
                        <flux:otp x-model="code" length="6" name="code" label="{{ __('Código OTP') }}" label:sr-only class="mx-auto" />
                    </div>
                </div>

                <div x-show="showRecoveryInput">
                    <div class="my-5">
                        <x-rh.text-field
                            name="recovery_code"
                            x-ref="recovery_code"
                            x-bind:required="showRecoveryInput"
                            autocomplete="one-time-code"
                            x-model="recovery_code"
                            :error="$errors->first('recovery_code')"
                        />
                    </div>
                </div>

                <x-rh.button type="submit" variant="primary" size="lg" class="w-full">
                    {{ __('Continuar') }}
                </x-rh.button>
            </div>

            <div class="mt-5 text-center text-sm">
                <span class="text-ink-muted">{{ __('o puedes') }}</span>
                <div class="inline font-medium underline cursor-pointer text-brand">
                    <span x-show="!showRecoveryInput" @click="toggleInput()">{{ __('ingresar con un código de recuperación') }}</span>
                    <span x-show="showRecoveryInput" @click="toggleInput()">{{ __('ingresar con un código de autenticación') }}</span>
                </div>
            </div>
        </form>
    </div>
</x-layouts::auth>
