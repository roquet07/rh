<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Mi expediente')] class extends Component {
    public function mount(): void
    {
        abort_unless(Auth::user()->empleado !== null, 403);
    }
}; ?>

<section class="w-full">
    @php($empleado = Auth::user()->empleado->load('puesto.departamento', 'contratos', 'comisiones'))

    <flux:heading size="xl">{{ __('Mi expediente') }}</flux:heading>
    <flux:subheading>{{ $empleado->numero_empleado }} — {{ $empleado->puesto->nombre }} ({{ $empleado->puesto->departamento->nombre }})</flux:subheading>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card>
            <flux:heading size="lg">{{ __('Datos personales') }}</flux:heading>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('RFC') }}</dt><dd>{{ $empleado->rfc }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('CURP') }}</dt><dd>{{ $empleado->curp }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('NSS') }}</dt><dd>{{ $empleado->nss ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Teléfono') }}</dt><dd>{{ $empleado->telefono ?: '—' }}</dd></div>
            </dl>
        </flux:card>

        <flux:card>
            <flux:heading size="lg">{{ __('Datos laborales') }}</flux:heading>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Fecha de ingreso') }}</dt><dd>{{ $empleado->fecha_ingreso->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Antigüedad') }}</dt><dd>{{ $empleado->aniosAntiguedad() }} {{ __('años') }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Salario diario') }}</dt><dd>${{ number_format((float) $empleado->salario_diario, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Jornada') }}</dt><dd class="capitalize">{{ $empleado->jornada }}</dd></div>
            </dl>
        </flux:card>
    </div>

    <flux:card class="mt-6">
        <flux:heading size="lg">{{ __('Mis contratos') }}</flux:heading>

        <flux:table class="mt-4">
            <flux:table.columns>
                <flux:table.column>{{ __('Tipo') }}</flux:table.column>
                <flux:table.column>{{ __('Vigencia') }}</flux:table.column>
                <flux:table.column>{{ __('Estatus') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($empleado->contratos as $contrato)
                    <flux:table.row wire:key="contrato-{{ $contrato->id }}">
                        <flux:table.cell class="capitalize">{{ str_replace('_', ' ', $contrato->tipo) }}</flux:table.cell>
                        <flux:table.cell>{{ $contrato->fecha_inicio->format('d/m/Y') }} — {{ $contrato->fecha_fin?->format('d/m/Y') ?? __('indefinido') }}</flux:table.cell>
                        <flux:table.cell class="capitalize">{{ $contrato->estatus }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3">{{ __('Sin contratos registrados.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</section>
