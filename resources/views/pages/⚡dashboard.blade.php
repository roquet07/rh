<?php

use App\Models\Contrato;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\PeriodoNomina;
use App\Models\SolicitudVacante;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    #[Computed]
    public function colaboradoresActivos(): int
    {
        return Empleado::query()->where('estatus', 'activo')->count();
    }

    #[Computed]
    public function altasEsteMes(): int
    {
        return Empleado::query()
            ->whereYear('fecha_ingreso', now()->year)
            ->whereMonth('fecha_ingreso', now()->month)
            ->count();
    }

    #[Computed]
    public function vacantesAbiertas(): int
    {
        return SolicitudVacante::query()->where('estatus', 'aprobada')->count();
    }

    #[Computed]
    public function solicitudesPendientes(): int
    {
        return SolicitudVacante::query()->where('estatus', 'pendiente')->count();
    }

    #[Computed]
    public function contratosPorVencer()
    {
        return Contrato::query()
            ->with('empleado')
            ->whereNotNull('fecha_fin')
            ->whereBetween('fecha_fin', [today(), today()->addDays(30)])
            ->orderBy('fecha_fin')
            ->get();
    }

    #[Computed]
    public function proximoPeriodo(): ?PeriodoNomina
    {
        return PeriodoNomina::query()->where('fecha_pago', '>=', today())->orderBy('fecha_pago')->first();
    }

    #[Computed]
    public function solicitudesRecientes()
    {
        return SolicitudVacante::query()->with(['departamento', 'puesto', 'solicitante'])->latest()->limit(5)->get();
    }

    #[Computed]
    public function plantillaPorDepartamento()
    {
        $conteos = Departamento::query()
            ->withCount(['puestos as empleados_count' => function ($query) {
                $query->join('empleados', 'empleados.puesto_id', '=', 'puestos.id')
                    ->where('empleados.estatus', 'activo');
            }])
            ->orderByDesc('empleados_count')
            ->get()
            ->filter(fn ($departamento) => $departamento->empleados_count > 0);

        $max = (int) $conteos->max('empleados_count');

        return $conteos->map(fn ($departamento) => [
            'nombre' => $departamento->nombre,
            'n' => $departamento->empleados_count,
            'pct' => $max > 0 ? round(($departamento->empleados_count / $max) * 100) : 0,
        ]);
    }

    /**
     * Próximos cumpleaños y aniversarios laborales en los siguientes 30 días.
     */
    #[Computed]
    public function proximosEventos()
    {
        $hoy = today();
        $eventos = collect();

        Empleado::query()->where('estatus', 'activo')->get()->each(function (Empleado $empleado) use ($hoy, $eventos) {
            $proximoCumple = $this->proximaOcurrencia($empleado->fecha_nacimiento, $hoy);

            if ($proximoCumple !== null && $proximoCumple->diffInDays($hoy) <= 30) {
                $eventos->push([
                    'nombre' => $empleado->nombreCompleto(),
                    'que' => __('Cumpleaños'),
                    'fecha' => $proximoCumple,
                ]);
            }

            $anios = $empleado->aniosAntiguedad($proximoAniversario = $this->proximaOcurrencia($empleado->fecha_ingreso, $hoy) ?? $hoy);

            if ($proximoAniversario !== null && $anios > 0 && $proximoAniversario->diffInDays($hoy) <= 30) {
                $eventos->push([
                    'nombre' => $empleado->nombreCompleto(),
                    'que' => __(':anios años en la empresa', ['anios' => $anios]),
                    'fecha' => $proximoAniversario,
                ]);
            }
        });

        return $eventos->sortBy('fecha')->take(5)->values();
    }

    private function proximaOcurrencia(?CarbonInterface $fecha, CarbonInterface $hoy): ?CarbonInterface
    {
        if ($fecha === null) {
            return null;
        }

        $esteAnio = $fecha->copy()->year($hoy->year);

        return $esteAnio->lt($hoy) ? $esteAnio->addYear() : $esteAnio;
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-end justify-between gap-6">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Hola, :nombre', ['nombre' => Auth::user()->name]) }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Resumen de Recursos Humanos') }} · {{ ucfirst(now()->locale('es')->isoFormat('dddd D MMM YYYY')) }}</p>
        </div>

        <div class="flex gap-2">
            @can('solicitudes.crear')
                <x-rh.button variant="secondary" href="{{ route('solicitudes.create') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.25 3H6.75A1.5 1.5 0 0 0 5.25 4.5v15A1.5 1.5 0 0 0 6.75 21h10.5a1.5 1.5 0 0 0 1.5-1.5V7.5L14.25 3ZM14.25 3v4.5h4.5M12 11.25v6M9 14.25h6"></path></svg>
                    {{ __('Solicitar vacante') }}
                </x-rh.button>
            @endcan

            @can('empleados.gestionar')
                <x-rh.button variant="primary" href="{{ route('empleados.create') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                    {{ __('Nuevo empleado') }}
                </x-rh.button>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <x-rh.stat-tile
            label="{{ __('Colaboradores activos') }}"
            value="{{ $this->colaboradoresActivos }}"
            icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path></svg>'
        >
            @if ($this->altasEsteMes > 0)
                <span class="text-success font-medium">+{{ $this->altasEsteMes }}</span> {{ __('altas este mes') }}
            @else
                {{ __('Sin altas este mes') }}
            @endif
        </x-rh.stat-tile>

        <x-rh.stat-tile
            label="{{ __('Vacantes abiertas') }}"
            value="{{ $this->vacantesAbiertas }}"
            :meta="$this->solicitudesPendientes > 0 ? __(':n solicitudes por aprobar', ['n' => $this->solicitudesPendientes]) : __('Sin solicitudes pendientes')"
            icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.25 3H6.75A1.5 1.5 0 0 0 5.25 4.5v15A1.5 1.5 0 0 0 6.75 21h10.5a1.5 1.5 0 0 0 1.5-1.5V7.5L14.25 3ZM14.25 3v4.5h4.5M12 11.25v6M9 14.25h6"></path></svg>'
        />

        <x-rh.stat-tile
            label="{{ __('Contratos por vencer') }}"
            value="{{ $this->contratosPorVencer->count() }}"
            meta="{{ __('En los próximos 30 días') }}"
            icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.25 3H6.75A1.5 1.5 0 0 0 5.25 4.5v15A1.5 1.5 0 0 0 6.75 21h10.5a1.5 1.5 0 0 0 1.5-1.5V7.5L14.25 3ZM14.25 3v4.5h4.5M9 12.75h6M9 15.75h6"></path></svg>'
        />

        <x-rh.stat-tile
            label="{{ __('Próxima nómina') }}"
            value="{{ $this->proximoPeriodo?->fecha_pago->format('d M') ?? __('Sin programar') }}"
            :meta="$this->proximoPeriodo ? ucfirst($this->proximoPeriodo->tipo).' · '.$this->colaboradoresActivos.' '.__('personas') : __('Crea un periodo de nómina')"
            icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6.75 3h10.5a1.5 1.5 0 0 1 1.5 1.5v15a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-15A1.5 1.5 0 0 1 6.75 3ZM8.25 6.75h7.5v3h-7.5zM8.25 13.5h.01M12 13.5h.01M15.75 13.5h.01M8.25 17.25h.01M12 17.25h.01M15.75 17.25h.01"></path></svg>'
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.6fr_1fr] gap-6">
        <x-rh.card :padded="false">
            <div class="flex items-center justify-between p-5">
                <div>
                    <h2 class="m-0 text-lg font-semibold">{{ __('Solicitudes de vacante') }}</h2>
                    <p class="m-0 text-sm text-ink-muted">{{ __('Pendientes de aprobación y movimientos recientes') }}</p>
                </div>

                @can('solicitudes.ver')
                    <a href="{{ route('solicitudes.index') }}" wire:navigate class="text-sm font-medium text-brand hover:underline">{{ __('Ver todas') }}</a>
                @endcan
            </div>

            @if ($this->solicitudesRecientes->isEmpty())
                <x-rh.empty-state
                    title="{{ __('Sin solicitudes') }}"
                    description="{{ __('Aún no se han registrado solicitudes de vacante.') }}"
                    icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.25 3H6.75A1.5 1.5 0 0 0 5.25 4.5v15A1.5 1.5 0 0 0 6.75 21h10.5a1.5 1.5 0 0 0 1.5-1.5V7.5L14.25 3ZM14.25 3v4.5h4.5M12 11.25v6M9 14.25h6"></path></svg>'
                />
            @else
                <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <x-rh.th>{{ __('Puesto') }}</x-rh.th>
                            <x-rh.th>{{ __('Departamento') }}</x-rh.th>
                            <x-rh.th>{{ __('Solicitó') }}</x-rh.th>
                            <x-rh.th>{{ __('Fecha') }}</x-rh.th>
                            <x-rh.th>{{ __('Estatus') }}</x-rh.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->solicitudesRecientes as $solicitud)
                            <tr class="hover:bg-surface-sunken">
                                <x-rh.td class="font-medium">{{ $solicitud->puesto->nombre ?? $solicitud->puesto_propuesto }}</x-rh.td>
                                <x-rh.td class="text-ink-muted">{{ $solicitud->departamento->nombre }}</x-rh.td>
                                <x-rh.td class="text-ink-muted">{{ $solicitud->solicitante->name }}</x-rh.td>
                                <x-rh.td class="text-ink-muted whitespace-nowrap">{{ $solicitud->created_at->format('d M') }}</x-rh.td>
                                <x-rh.td><x-rh.badge :estado="$solicitud->estatus" /></x-rh.td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </x-rh.card>

        <x-rh.card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="m-0 text-lg font-semibold">{{ __('Contratos por vencer') }}</h2>

                @can('contratos.ver')
                    <a href="{{ route('contratos.index') }}" wire:navigate class="text-sm font-medium text-brand hover:underline">{{ __('Ver contratos') }}</a>
                @endcan
            </div>

            @if ($this->contratosPorVencer->isEmpty())
                <x-rh.empty-state
                    title="{{ __('Nada por vencer') }}"
                    description="{{ __('No hay contratos por vencer en los próximos 30 días.') }}"
                    icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.25 3H6.75A1.5 1.5 0 0 0 5.25 4.5v15A1.5 1.5 0 0 0 6.75 21h10.5a1.5 1.5 0 0 0 1.5-1.5V7.5L14.25 3ZM14.25 3v4.5h4.5M9 12.75h6M9 15.75h6"></path></svg>'
                />
            @else
                <ul class="list-none m-0 p-0 flex flex-col">
                    @foreach ($this->contratosPorVencer as $contrato)
                        @php($diasRestantes = today()->diffInDays($contrato->fecha_fin, false))
                        <li class="flex items-center gap-3 py-3 border-b border-line last:border-b-0">
                            <x-rh.avatar :name="$contrato->empleado->nombreCompleto()" />

                            <div class="flex-1 min-w-0 flex flex-col">
                                <span class="text-sm font-medium truncate">{{ $contrato->empleado->nombreCompleto() }}</span>
                                <span class="text-xs text-ink-muted">{{ str_replace('_', ' ', ucfirst($contrato->tipo)) }} · {{ __('vence') }} {{ $contrato->fecha_fin->format('d M') }}</span>
                            </div>

                            <x-rh.badge
                                :tone="$diasRestantes <= 10 ? 'danger' : ($diasRestantes <= 25 ? 'warning' : 'info')"
                                label="{{ __('En :n días', ['n' => $diasRestantes]) }}"
                            />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-rh.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.6fr_1fr] gap-6">
        <x-rh.card>
            <div class="flex items-baseline justify-between mb-4">
                <h2 class="m-0 text-lg font-semibold">{{ __('Plantilla por departamento') }}</h2>
                <span class="text-sm text-ink-muted">{{ $this->colaboradoresActivos }} {{ __('colaboradores activos') }}</span>
            </div>

            @if ($this->plantillaPorDepartamento->isEmpty())
                <x-rh.empty-state
                    title="{{ __('Sin datos') }}"
                    description="{{ __('Aún no hay empleados asignados a un departamento.') }}"
                    icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3.75 21h16.5M5.25 21V4.5h9V21M14.25 9h4.5v12M8.25 8.25h3M8.25 11.25h3M8.25 14.25h3"></path></svg>'
                />
            @else
                <ul class="list-none m-0 p-0 flex flex-col gap-3.5">
                    @foreach ($this->plantillaPorDepartamento as $departamento)
                        <li class="grid grid-cols-[160px_1fr_40px] items-center gap-4 text-sm">
                            <span class="text-ink-muted truncate">{{ $departamento['nombre'] }}</span>
                            <span class="h-2.5 rounded-full bg-surface-sunken overflow-hidden">
                                <span class="block h-full rounded-full bg-brand" style="width: {{ $departamento['pct'] }}%"></span>
                            </span>
                            <span class="text-right font-medium tabular-nums">{{ $departamento['n'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-rh.card>

        <x-rh.card>
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Cumpleaños y aniversarios') }}</h2>

            @if ($this->proximosEventos->isEmpty())
                <x-rh.empty-state
                    title="{{ __('Nada próximo') }}"
                    description="{{ __('No hay cumpleaños ni aniversarios en los próximos 30 días.') }}"
                    icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25"></path></svg>'
                />
            @else
                <ul class="list-none m-0 p-0 flex flex-col gap-3">
                    @foreach ($this->proximosEventos as $evento)
                        <li class="flex items-center gap-3 p-3 rounded-lg bg-surface-sunken">
                            <span aria-hidden="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warm text-on-warm">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"></path></svg>
                            </span>

                            <div class="flex-1 flex flex-col">
                                <span class="text-sm font-medium">{{ $evento['nombre'] }}</span>
                                <span class="text-xs text-ink-muted">{{ $evento['que'] }}</span>
                            </div>

                            <span class="text-sm font-medium whitespace-nowrap">{{ $evento['fecha']->format('d M') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-rh.card>
    </div>
</section>
