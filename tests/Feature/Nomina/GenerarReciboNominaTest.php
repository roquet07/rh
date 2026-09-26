<?php

use App\Actions\Nomina\GenerarReciboNomina;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\EmpresaConfig;
use App\Models\NominaDetalle;
use App\Models\PeriodoNomina;
use App\Models\Puesto;
use Illuminate\Support\Facades\Storage;

test('genera y almacena el PDF del recibo de nómina', function () {
    Storage::fake('local');

    EmpresaConfig::query()->create([
        'razon_social' => 'Acme S.A. de C.V.',
        'rfc' => 'ACM010101AB1',
        'domicilio_fiscal' => 'Av. Siempre Viva 123',
    ]);

    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $empleado = Empleado::factory()->create(['puesto_id' => $puesto->id]);

    $periodo = PeriodoNomina::query()->create([
        'tipo' => 'quincenal',
        'fecha_inicio' => now()->startOfMonth()->toDateString(),
        'fecha_fin' => now()->startOfMonth()->addDays(14)->toDateString(),
        'fecha_pago' => now()->startOfMonth()->addDays(15)->toDateString(),
        'estatus' => 'abierto',
    ]);

    $detalle = NominaDetalle::query()->create([
        'periodo_nomina_id' => $periodo->id,
        'empleado_id' => $empleado->id,
        'dias_trabajados' => 15,
        'salario_diario' => 400,
        'sdi' => 415,
        'percepcion_salario' => 6000,
        'percepcion_comision' => 0,
        'percepcion_aguinaldo' => 0,
        'percepcion_ptu' => 0,
        'percepcion_otras' => 0,
        'deduccion_isr' => 300,
        'deduccion_imss' => 100,
        'deduccion_otras' => 0,
        'total_percepciones' => 6000,
        'total_deducciones' => 400,
        'neto_pagar' => 5600,
    ]);

    $path = (new GenerarReciboNomina)->generar($detalle);

    Storage::disk('local')->assertExists($path);
    expect(Storage::disk('local')->size($path))->toBeGreaterThan(0);
    expect($detalle->refresh()->recibo_pdf_path)->toBe($path);
});
