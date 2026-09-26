<?php

use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\EmpresaConfig;
use App\Support\ContratoRules;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Contrato laboral')] class extends Component
{
    public Empleado $empleado;

    public ?Contrato $contrato = null;

    public string $tipo = 'indeterminado';

    public string $fecha_inicio = '';

    public string $duracion_meses = '3';

    public string $fecha_celebracion = '';

    public string $salario_diario = '';

    public string $salario_mensual = '';

    public string $jornada = 'diurna';

    public string $lugar_trabajo = '';

    public string $nacionalidad = '';

    public string $sexo = '';

    public string $funciones = '';

    public string $horario_trabajo = '';

    public string $horas_semanales = '48';

    public string $descanso_minutos = '60';

    public string $dia_descanso = 'domingo';

    public string $periodicidad_pago = 'quincenal';

    public string $dias_pago = '15 y último día de cada mes';

    public string $forma_pago = 'depósito bancario';

    public string $clausulas_adicionales = '';

    /** @var array<int, array{nombre: string, parentesco: string, porcentaje: string|float|int}> */
    public array $beneficiarios = [['nombre' => '', 'parentesco' => '', 'porcentaje' => '100']];

    public function mount(?Empleado $empleado = null, ?Contrato $contrato = null): void
    {
        $this->authorize('contratos.gestionar');

        if ($contrato?->exists) {
            abort_if($contrato->estatus === 'firmado', 403, 'Un contrato firmado no se puede modificar.');
            $this->contrato = $contrato;
            $empleado = $contrato->empleado;
        }

        abort_unless($empleado?->exists, 404);
        $this->empleado = $empleado;
        $this->fecha_inicio = now()->toDateString();
        $this->fecha_celebracion = now()->toDateString();
        $this->salario_diario = (string) $empleado->salario_diario;
        $this->updatedSalarioDiario();
        $this->jornada = $empleado->jornada;
        $this->horas_semanales = (string) match ($this->jornada) {
            'nocturna' => 42,
            'mixta' => 45,
            default => 48,
        };
        $this->lugar_trabajo = $empleado->sucursal ?: (string) EmpresaConfig::current()->domicilio_fiscal;
        $this->funciones = (string) $empleado->puesto->descripcion;

        if ($this->contrato !== null) {
            foreach (array_keys(ContratoRules::rules()) as $campo) {
                if (str_contains($campo, '*') || $campo === 'beneficiarios') {
                    continue;
                }
                $valor = $this->contrato->getAttribute($campo);
                if ($valor !== null) {
                    $this->{$campo} = $valor instanceof \Carbon\CarbonInterface ? $valor->toDateString() : (string) $valor;
                }
            }
            $this->beneficiarios = $this->contrato->beneficiarios ?? $this->beneficiarios;
        }
    }

    public function updatedSalarioDiario(): void
    {
        if (is_numeric($this->salario_diario)) {
            $this->salario_mensual = number_format((float) $this->salario_diario * 30, 2, '.', '');
        }
    }

    public function updatedSalarioMensual(): void
    {
        if (is_numeric($this->salario_mensual)) {
            $this->salario_diario = number_format((float) $this->salario_mensual / 30, 2, '.', '');
        }
    }

    #[Computed]
    public function fechaFin(): ?string
    {
        if ($this->tipo !== 'determinado' || Validator::make([
            'inicio' => $this->fecha_inicio,
            'meses' => $this->duracion_meses,
        ], ['inicio' => 'required|date_format:Y-m-d', 'meses' => 'required|integer|between:1,120'])->fails()) {
            return null;
        }

        return CarbonImmutable::parse($this->fecha_inicio)->addMonthsNoOverflow((int) $this->duracion_meses)->subDay()->toDateString();
    }

    public function agregarBeneficiario(): void
    {
        $this->authorize('contratos.gestionar');
        if (count($this->beneficiarios) < 10) {
            $this->beneficiarios[] = ['nombre' => '', 'parentesco' => '', 'porcentaje' => ''];
        }
    }

    public function quitarBeneficiario(int $indice): void
    {
        $this->authorize('contratos.gestionar');
        unset($this->beneficiarios[$indice]);
        $this->beneficiarios = array_values($this->beneficiarios);
    }

    public function save(): void
    {
        $this->authorize('contratos.gestionar');
        if ($this->contrato !== null) {
            $this->contrato->refresh();
            abort_if($this->contrato->estatus === 'firmado', 403, 'Un contrato firmado no se puede modificar.');
        }

        $validated = $this->validate(ContratoRules::rules(), [], ContratoRules::attributes());
        ContratoRules::validarCondiciones($validated);
        $validated['duracion_meses'] = $this->tipo === 'determinado' ? (int) $this->duracion_meses : null;
        $validated['fecha_fin'] = $this->fechaFin();
        $validated['estatus'] = 'borrador';
        $validated['pdf_path'] = null;

        if ($this->contrato !== null) {
            $this->contrato->update($validated);
            $contrato = $this->contrato;
        } else {
            $contrato = Contrato::query()->create($validated + [
                'empleado_id' => $this->empleado->id,
                'solicitud_vacante_id' => $this->empleado->solicitud_vacante_id,
                'puesto_id' => $this->empleado->puesto_id,
            ]);
        }

        Flux::toast(variant: 'success', text: __('Contrato guardado en borrador.'));
        $this->redirect(route('contratos.show', $contrato), navigate: true);
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div>
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ $contrato ? __('Editar contrato') : __('Nuevo contrato') }}</h1>
        <p class="mt-1 text-ink-muted">{{ $empleado->nombreCompleto() }} — {{ $empleado->numero_empleado }}</p>
    </div>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        @if ($errors->any())
            <div role="alert" class="rounded-md border border-danger p-4 text-danger">
                <p class="font-semibold">Revisa los datos del contrato:</p>
                <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li wire:key="error-{{ $loop->index }}">{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <x-rh.card class="space-y-5">
            <flux:heading size="lg">Vigencia</flux:heading>
            <flux:select wire:model.live="tipo" label="Tipo de contrato" required>
                <option value="indeterminado">Tiempo indeterminado</option>
                <option value="determinado">Tiempo determinado</option>
            </flux:select>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model.live="fecha_inicio" type="date" label="Fecha de inicio" required />
                @if ($tipo === 'determinado')
                    <flux:input wire:model.live="duracion_meses" type="number" min="1" max="120" label="Duración en meses" required />
                @endif
            </div>
            @if ($tipo === 'determinado')
                <flux:text>Último día de vigencia: <strong>{{ $this->fechaFin ?? 'Selecciona el inicio y la duración' }}</strong>. El plazo incluye el día de inicio y termina el día anterior al aniversario mensual. Por ejemplo: del 1 de octubre al 31 de diciembre para 3 meses.</flux:text>
                <flux:text>Esta duración corresponde al plazo del contrato determinado. No establece por sí sola un periodo de prueba legal.</flux:text>
            @else
                <flux:text>Vigencia desde la fecha de inicio, sin fecha de terminación.</flux:text>
            @endif
            <flux:input wire:model="fecha_celebracion" type="date" label="Fecha de celebración del documento" required />
        </x-rh.card>

        <x-rh.card class="space-y-5">
            <flux:heading size="lg">Datos para las declaraciones</flux:heading>
            <flux:text>Nombre completo, RFC, CURP, NSS, edad, estado civil y domicilio se toman del expediente del empleado. Los datos legales de la empresa se toman de su configuración.</flux:text>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="nacionalidad" label="Nacionalidad" required />
                <flux:input wire:model="sexo" label="Sexo (según expediente)" required />
            </div>
            <flux:textarea wire:model="funciones" label="Funciones del puesto" rows="6" required />
            <flux:input wire:model="lugar_trabajo" label="Lugar de trabajo" required />
        </x-rh.card>

        <x-rh.card class="space-y-5">
            <flux:heading size="lg">Jornada y pago</flux:heading>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model="jornada" label="Jornada" required>
                    <option value="diurna">Diurna</option><option value="nocturna">Nocturna</option><option value="mixta">Mixta</option>
                </flux:select>
                <flux:input wire:model="horas_semanales" type="number" min="1" max="48" label="Horas semanales" required />
                <flux:input wire:model="horario_trabajo" label="Horario y días de trabajo" placeholder="Lunes a sábado, de 09:00 a 18:00" required />
                <flux:input wire:model="descanso_minutos" type="number" min="30" max="180" label="Minutos para descanso o alimentos" required />
                <flux:input wire:model="dia_descanso" label="Día de descanso semanal" required />
                <flux:input wire:model.blur="salario_mensual" type="number" step="0.01" min="0.01" label="Salario mensual bruto (MXN)" required />
                <flux:input wire:model.blur="salario_diario" type="number" step="0.01" min="0.01" label="Salario diario (MXN)" required />
                <flux:select wire:model="periodicidad_pago" label="Periodicidad de pago" required>
                    <option value="semanal">Semanal</option><option value="quincenal">Quincenal</option>
                </flux:select>
                <flux:input wire:model="dias_pago" label="Días de pago" required />
                <flux:input wire:model="forma_pago" label="Forma de pago" required />
            </div>
            <flux:text>Al cambiar el salario mensual o diario se calcula su equivalente con base de 30 días.</flux:text>
        </x-rh.card>

        <x-rh.card class="space-y-5">
            <flux:heading size="lg">Beneficiarios</flux:heading>
            <flux:text>Los porcentajes deben sumar 100%.</flux:text>
            @foreach ($beneficiarios as $indice => $beneficiario)
                <div wire:key="beneficiario-{{ $indice }}" class="grid gap-3 sm:grid-cols-4">
                    <flux:input wire:model="beneficiarios.{{ $indice }}.nombre" label="Nombre completo" required />
                    <flux:input wire:model="beneficiarios.{{ $indice }}.parentesco" label="Parentesco" required />
                    <flux:input wire:model="beneficiarios.{{ $indice }}.porcentaje" type="number" min="0.01" max="100" step="0.01" label="Porcentaje" required />
                    <flux:button type="button" wire:click="quitarBeneficiario({{ $indice }})" class="self-end">Quitar</flux:button>
                </div>
            @endforeach
            <flux:button type="button" wire:click="agregarBeneficiario">Agregar beneficiario</flux:button>
        </x-rh.card>

        <x-rh.card class="space-y-5">
            <flux:textarea wire:model="clausulas_adicionales" label="Cláusulas adicionales (opcional)" rows="4" />
            <div class="flex justify-end gap-3">
                <x-rh.button variant="secondary" href="{{ $contrato ? route('contratos.show', $contrato) : route('empleados.show', $empleado) }}">Cancelar</x-rh.button>
                <flux:button type="submit" variant="primary">Guardar contrato</flux:button>
            </div>
        </x-rh.card>
    </form>
</section>
