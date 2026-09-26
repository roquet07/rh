<?php

use App\Actions\Contratos\GenerarContratoPdf;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Empleado;
use App\Models\EmpresaConfig;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoHrSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('la carga demo genera ambos tipos de contrato completos y documentos disponibles', function () {
    Storage::fake('local');
    $this->travelTo(now()->setDate(2026, 9, 26)->startOfDay());

    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('users', 4);
    $this->assertDatabaseCount('empleados', 7);
    $this->assertDatabaseCount('contratos', 7);
    $this->assertDatabaseCount('documentos', 22);
    expect(Contrato::query()->whereNotIn('tipo', ['determinado', 'indeterminado'])->exists())->toBeFalse();
    $temporal = Contrato::query()->where('tipo', 'determinado')->sole();
    expect($temporal->duracion_meses)->toBe(3);
    expect($temporal->fecha_fin->toDateString())->toBe('2026-12-05');
    expect(Contrato::query()->where('tipo', 'indeterminado')->whereNotNull('fecha_fin')->exists())->toBeFalse();

    foreach (Contrato::query()->get() as $contrato) {
        if ($contrato->estatus === 'borrador') {
            (new GenerarContratoPdf)->generar($contrato);
        }
        Storage::disk('local')->assertExists($contrato->pdf_path);
        expect(Storage::disk('local')->get($contrato->pdf_path))->toStartWith('%PDF-');
    }
    foreach (Documento::query()->get() as $documento) {
        Storage::disk('local')->assertExists($documento->path);
    }
});

test('se puede ejecutar directamente dos veces sin duplicados ni sobrescribir contratos firmados', function () {
    Storage::fake('local');
    $this->travelTo(now()->setDate(2026, 9, 26)->startOfDay());
    $this->seed(DemoHrSeeder::class);
    $firmado = Contrato::query()->where('estatus', 'firmado')->firstOrFail();
    $pdf = Storage::disk('local')->get($firmado->pdf_path);
    User::query()->where('email', 'rh@rh.test')->firstOrFail()->update(['password' => 'clave-personalizada']);

    $this->seed(DemoHrSeeder::class);

    foreach (['users' => 4, 'empleados' => 7, 'departamentos' => 4, 'puestos' => 7, 'documento_tipos' => 5, 'contratos' => 7, 'solicitudes_vacante' => 3, 'comisiones' => 2, 'documentos' => 22, 'periodos_nomina' => 1] as $tabla => $total) {
        $this->assertDatabaseCount($tabla, $total);
    }
    expect(Storage::disk('local')->get($firmado->pdf_path))->toBe($pdf);
    expect(Hash::check('clave-personalizada', User::query()->where('email', 'rh@rh.test')->sole()->password))->toBeTrue();
});

test('completa campos nuevos de la empresa demo existente sin borrar sus datos capturados', function () {
    Storage::fake('local');
    EmpresaConfig::query()->create([
        'id' => 1,
        'razon_social' => 'Talento RH Demo S.A. de C.V.',
        'rfc' => 'TRD010101AB1',
        'domicilio_fiscal' => 'Domicilio demo personalizado',
        'representante_legal' => 'Representante personalizado',
    ]);

    $this->seed(DemoHrSeeder::class);

    expect(EmpresaConfig::current()->correo_privacidad)->toBe('privacidad@rh.test');
    expect(EmpresaConfig::current()->domicilio_fiscal)->toBe('Domicilio demo personalizado');
    expect(EmpresaConfig::current()->representante_legal)->toBe('Representante personalizado');
});

test('no sustituye la configuración de una empresa distinta por los datos demo', function () {
    Storage::fake('local');
    $empresa = EmpresaConfig::factory()->create(['id' => 1]);
    $datos = $empresa->refresh()->getAttributes();

    $this->seed(DemoHrSeeder::class);

    expect($empresa->refresh()->getAttributes())->toBe($datos);
});

test('el seeder demo no crea datos si se ejecuta directamente en producción', function () {
    Storage::fake('local');
    $this->app->instance('env', 'production');

    app(DemoHrSeeder::class)->run();

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('contratos', 0);
    $this->assertDatabaseCount('empresa_config', 0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('recupera una demo anterior sin duplicar empleados vinculados ni dejar contratos incompatibles', function () {
    Storage::fake('local');
    $this->seed(DemoHrSeeder::class);
    foreach (Empleado::query()->get() as $empleado) {
        $empleado->update(['numero_empleado' => 'EMP-ANTERIOR-'.$empleado->id]);
    }
    $contrato = Contrato::query()->where('tipo', 'determinado')->sole();
    Storage::disk('local')->delete($contrato->pdf_path);
    $contrato->update([
        'tipo' => 'periodo_prueba', 'duracion_meses' => null, 'fecha_celebracion' => null,
        'salario_mensual' => null, 'funciones' => null, 'beneficiarios' => null,
        'fecha_fin' => now()->addDays(10),
    ]);

    $this->seed(DemoHrSeeder::class);

    $this->assertDatabaseCount('empleados', 7);
    $this->assertDatabaseCount('contratos', 7);
    expect($contrato->refresh()->tipo)->toBe('determinado');
    expect($contrato->duracion_meses)->toBe(3);
    expect($contrato->estatus)->toBe('generado');
    Storage::disk('local')->assertExists($contrato->pdf_path);
});
