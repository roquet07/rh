<?php

use App\Actions\Contratos\GenerarContratoPdf;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\EmpresaConfig;
use App\Models\Puesto;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;

test('genera y almacena el PDF del contrato', function (string $tipo) {
    Storage::fake('local');
    EmpresaConfig::factory()->create();
    $empleado = Empleado::factory()->for(Puesto::factory()->state(['nombre' => 'Coordinación Comercial']))->create([
        'nombre_completo' => 'Alicia', 'apellido_paterno' => 'García', 'apellido_materno' => 'López',
        'fecha_nacimiento' => '1994-05-03', 'estado_civil' => 'Soltera',
        'domicilio' => 'Calle de Ejemplo 25', 'colonia' => 'Centro', 'municipio' => 'Guadalajara',
        'estado_direccion' => 'Jalisco', 'codigo_postal' => '44100',
        'rfc' => 'GALA940503AB1', 'curp' => 'GALA940503MJCRPC01', 'nss' => '12345678901',
    ]);
    $factory = Contrato::factory()->for($empleado);
    $contrato = $tipo === 'determinado' ? $factory->determinado()->create() : $factory->create();
    $html = '';
    View::composer('pdf.contrato', function ($view) use (&$html) {
        $html = app('blade.compiler')->render(file_get_contents(resource_path('views/pdf/contrato.blade.php')), $view->getData());
    });

    $path = (new GenerarContratoPdf)->generar($contrato);

    Storage::disk('local')->assertExists($path);
    expect(Storage::disk('local')->get($path))->toStartWith('%PDF-');
    expect($contrato->refresh()->estatus)->toBe('generado');
    expect($html)->toContain('Empresa de Prueba S.A. de C.V.', 'privacidad@example.test', 'https://example.test/privacidad', 'Persona Beneficiaria', 'veinte mil pesos 00/100 M.N.', e($contrato->empleado->nombreCompleto()), 'AVISO DE PRIVACIDAD SIMPLIFICADO', 'CONTRATO DE CONFIDENCIALIDAD', 'DÉCIMA QUINTA', 'SEGUNDA BIS 4', 'DÉCIMA TERCERA. CONTROVERSIAS')
        ->not->toContain('FIBRA STAR', 'LIZBET', 'LUIS MARIO', 'MOPL940503', '8170', 'fibrastarmx.com', '27 DE FEBRERO DE 2025');
    $vigencia = $tipo === 'determinado' ? '31/12/2026' : 'sin fecha de terminación';
    expect($html)->toContain($vigencia);

    if ($directorio = getenv('CONTRATO_PREVIEW_DIR')) {
        File::ensureDirectoryExists($directorio);
        File::put($directorio.'/contrato-'.$tipo.'.pdf', Storage::disk('local')->get($path));
        File::put($directorio.'/contrato-'.$tipo.'.html', $html);
    }
})->with(['determinado', 'indeterminado']);

test('impide generar cuando faltan datos de la empresa sin cambiar el estado', function () {
    Storage::fake('local');
    $contrato = Contrato::factory()->create();

    try {
        (new GenerarContratoPdf)->generar($contrato);
        $this->fail('Debió solicitar la configuración de la empresa.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKeys(['razon_social', 'representante_legal', 'escritura_constitutiva', 'correo_privacidad']);
    }

    Storage::disk('local')->assertDirectoryEmpty('contratos');
    expect($contrato->refresh()->estatus)->toBe('borrador');
});

test('no permite regenerar un contrato firmado ni reemplazar su PDF', function () {
    Storage::fake('local');
    $contrato = Contrato::factory()->create(['estatus' => 'firmado', 'pdf_path' => 'contratos/firmado.pdf']);
    Storage::disk('local')->put('contratos/firmado.pdf', 'documento original');

    expect(fn () => (new GenerarContratoPdf)->generar($contrato))->toThrow(ValidationException::class);
    expect(Storage::disk('local')->get('contratos/firmado.pdf'))->toBe('documento original');
});

test('rechaza vigencia inconsistente o datos incompletos del empleado', function (string $caso) {
    Storage::fake('local');
    EmpresaConfig::factory()->create();
    $contrato = Contrato::factory()->determinado()->create();
    if ($caso === 'fecha_fin') {
        $contrato->update(['fecha_fin' => '2027-01-15']);
    } else {
        $contrato->empleado->update(['domicilio' => null]);
    }

    expect(fn () => (new GenerarContratoPdf)->generar($contrato))->toThrow(ValidationException::class);
    expect($contrato->refresh()->pdf_path)->toBeNull();
})->with(['fecha_fin', 'domicilio']);

test('escapa el contenido capturado en las cláusulas y funciones', function () {
    Storage::fake('local');
    EmpresaConfig::factory()->create();
    $contrato = Contrato::factory()->create(['funciones' => '<script>alert(1)</script>', 'clausulas_adicionales' => '<img src="https://example.test/secreto">']);
    $html = '';
    View::composer('pdf.contrato', function ($view) use (&$html) {
        $html = app('blade.compiler')->render(file_get_contents(resource_path('views/pdf/contrato.blade.php')), $view->getData());
    });

    (new GenerarContratoPdf)->generar($contrato);

    expect($html)->toContain('&lt;script&gt;', '&lt;img')->not->toContain('<script>', '<img');
});
