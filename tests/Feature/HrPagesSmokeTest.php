<?php

use App\Models\Comision;
use App\Models\Contrato;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\PeriodoNomina;
use App\Models\Puesto;
use App\Models\SolicitudVacante;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\TablaVacacionesAntiguedadSeeder;

beforeEach(function () {
    $this->seed([RolesPermissionsSeeder::class, TablaVacacionesAntiguedadSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Administrador');
});

test('todas las páginas de listado del sistema de RH cargan sin errores', function () {
    $this->actingAs($this->admin);

    $rutasSimples = [
        'departamentos.index',
        'puestos.index',
        'documento-tipos.index',
        'empresa.config',
        'empleados.index',
        'empleados.create',
        'solicitudes.index',
        'solicitudes.create',
        'contratos.index',
        'comisiones.index',
        'nomina.periodos',
        'config-fiscal.isr',
        'config-fiscal.imss',
        'config-fiscal.vacaciones',
        'admin.usuarios',
        'admin.roles',
    ];

    foreach ($rutasSimples as $ruta) {
        $this->get(route($ruta))->assertOk();
    }
});

test('las páginas de detalle cargan sin errores', function () {
    $this->actingAs($this->admin);

    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $empleado = Empleado::factory()->create(['puesto_id' => $puesto->id]);

    $solicitud = SolicitudVacante::query()->create([
        'folio' => 'SOL-2026-9999',
        'solicitante_user_id' => $this->admin->id,
        'departamento_id' => $puesto->departamento_id,
        'puesto_id' => $puesto->id,
        'salario_propuesto' => 400,
        'numero_plazas' => 1,
        'justificacion' => 'Prueba de humo.',
        'urgencia' => 'baja',
        'estatus' => 'pendiente',
    ]);

    $contrato = Contrato::query()->create([
        'empleado_id' => $empleado->id,
        'puesto_id' => $puesto->id,
        'tipo' => 'indeterminado',
        'fecha_inicio' => now()->toDateString(),
        'salario_diario' => 400,
        'jornada' => 'diurna',
        'lugar_trabajo' => 'Oficina Central',
        'estatus' => 'borrador',
    ]);

    Comision::query()->create([
        'empleado_id' => $empleado->id,
        'monto_base' => 1000,
        'porcentaje_aplicado' => 5,
        'monto_comision' => 50,
        'fecha_periodo_inicio' => now()->startOfMonth()->toDateString(),
        'fecha_periodo_fin' => now()->endOfMonth()->toDateString(),
        'estatus' => 'pendiente',
        'capturado_por_user_id' => $this->admin->id,
    ]);

    $periodo = PeriodoNomina::query()->create([
        'tipo' => 'quincenal',
        'fecha_inicio' => now()->startOfMonth()->toDateString(),
        'fecha_fin' => now()->startOfMonth()->addDays(14)->toDateString(),
        'fecha_pago' => now()->startOfMonth()->addDays(15)->toDateString(),
        'estatus' => 'abierto',
    ]);

    $this->get(route('empleados.show', $empleado))->assertOk();
    $this->get(route('empleados.edit', $empleado))->assertOk();
    $this->get(route('solicitudes.show', $solicitud))->assertOk();
    $this->get(route('contratos.create', $empleado))->assertOk();
    $this->get(route('contratos.show', $contrato))->assertOk();
    $this->get(route('nomina.periodo-detalle', $periodo))->assertOk();
});

test('mi-expediente carga para un usuario con empleado vinculado', function () {
    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $user = User::factory()->create();
    Empleado::factory()->create(['puesto_id' => $puesto->id, 'user_id' => $user->id]);

    $this->actingAs($user)->get(route('mi-expediente'))->assertOk();
});
