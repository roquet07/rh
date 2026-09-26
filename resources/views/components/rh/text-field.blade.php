@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'required' => false,
    'error' => null,
    'hint' => null,
    'mono' => false,
    'prefix' => null,
    'suffix' => null,
])

@php
    $fieldId = $id ?? $name;
    $hasError = filled($error);
@endphp

<div class="flex flex-col gap-1.5">
    @if ($label)
        <label for="{{ $fieldId }}" class="text-sm font-medium text-ink">
            {{ $label }}
            @if ($required)
                <span aria-hidden="true" class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="relative flex items-center">
        @if ($prefix)
            <span aria-hidden="true" class="absolute left-3 text-sm text-ink-muted">{{ $prefix }}</span>
        @endif

        <input
            id="{{ $fieldId }}"
            @if ($name) name="{{ $name }}" @endif
            type="{{ $type }}"
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @elseif ($hint) aria-describedby="{{ $fieldId }}-hint" @endif
            {{ $attributes->merge([
                'class' => 'h-10 w-full rounded-md border bg-surface-raised px-3 text-sm text-ink placeholder:text-ink-muted focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus '
                    .($hasError ? 'border-danger' : 'border-line-strong')
                    .($prefix ? ' pl-7' : '')
                    .($suffix ? ' pr-14' : '')
                    .($mono ? ' font-mono uppercase' : ''),
            ]) }}
        />

        @if ($suffix)
            <span aria-hidden="true" class="absolute right-3 text-xs font-medium text-ink-muted">{{ $suffix }}</span>
        @endif
    </div>

    @if ($hasError)
        <p id="{{ $fieldId }}-error" class="flex items-center gap-1.5 text-xs font-medium text-danger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"></path></svg>
            {{ $error }}
        </p>
    @elseif ($hint)
        <p id="{{ $fieldId }}-hint" class="text-xs text-ink-muted">{{ $hint }}</p>
    @endif
</div>
