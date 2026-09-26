@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'error' => null,
    'strength' => false,
])

@php
    $fieldId = $id ?? $name;
    $hasError = filled($error);
@endphp

<div
    class="flex flex-col gap-1.5"
    x-data="{
        show: false,
        pw: '',
        score() {
            let s = 0;
            if (this.pw.length >= 8) s++;
            if (/[0-9]/.test(this.pw)) s++;
            if (/[^A-Za-z0-9]/.test(this.pw)) s++;
            if (this.pw.length >= 12 && /[A-Z]/.test(this.pw)) s++;
            if (this.pw.length && !s) s = 1;
            return s;
        },
        tone() {
            const s = this.score();
            return s >= 4 ? 'var(--success)' : s >= 2 ? 'var(--warning)' : 'var(--danger)';
        },
        label() {
            return ['Sin capturar', 'Débil', 'Aceptable', 'Buena', 'Segura'][this.score()];
        },
    }"
>
    @if ($label)
        <label for="{{ $fieldId }}" class="text-sm font-medium text-ink">
            {{ $label }}
            @if ($required)
                <span aria-hidden="true" class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="relative flex items-center">
        <input
            id="{{ $fieldId }}"
            @if ($name) name="{{ $name }}" @endif
            :type="show ? 'text' : 'password'"
            @if ($strength) @input="pw = $event.target.value" @endif
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
            {{ $attributes->merge([
                'class' => 'h-10 w-full rounded-md border bg-surface-raised px-3 pr-11 text-sm text-ink placeholder:text-ink-muted focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus '
                    .($hasError ? 'border-danger' : 'border-line-strong'),
            ]) }}
        />

        <button
            type="button"
            @click="show = !show"
            :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            class="absolute right-1 flex h-8 w-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-ink"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path></svg>
        </button>
    </div>

    @if ($strength)
        <div class="flex flex-col gap-1.5">
            <div aria-hidden="true" class="grid grid-cols-4 gap-1">
                <template x-for="i in 4" :key="i">
                    <span class="h-1 rounded-full" :style="{ background: score() >= i ? tone() : 'var(--line)' }"></span>
                </template>
            </div>
            <p class="text-xs text-ink-muted">
                Seguridad: <strong class="font-medium" :style="{ color: pw.length ? tone() : 'var(--ink-muted)' }" x-text="label()"></strong> · Usa 8 caracteres o más, con números y símbolos.
            </p>
        </div>
    @endif

    @if ($hasError)
        <p id="{{ $fieldId }}-error" class="flex items-center gap-1.5 text-xs font-medium text-danger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"></path></svg>
            {{ $error }}
        </p>
    @endif
</div>
