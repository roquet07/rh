@props([
    'estado' => null,
    'tone' => null,
    'label' => null,
])

@php
    use App\Support\EstadoTono;

    $resolvedTone = $tone ?? ($estado !== null ? EstadoTono::tono($estado) : 'neutral');
    $resolvedLabel = $label ?? ($estado !== null ? EstadoTono::etiqueta($estado) : '');

    $classes = match ($resolvedTone) {
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'info' => 'bg-info-soft text-info',
        default => 'bg-surface-sunken text-ink-muted',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex h-6 items-center gap-1.5 rounded-full px-2.5 text-xs font-medium whitespace-nowrap '.$classes]) }}>
    <span aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ $resolvedLabel }}
</span>
