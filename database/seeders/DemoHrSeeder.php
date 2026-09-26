<?php

namespace Database\Seeders;

use App\Actions\Contratos\GenerarContratoPdf;
use App\Models\Comision;
use App\Models\Contrato;
use App\Models\Departamento;
use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Empleado;
use App\Models\EmpresaConfig;
use App\Models\PeriodoNomina;
use App\Models\Puesto;
use App\Models\SolicitudVacante;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoHrSeeder extends Seeder
{
    /**
     * Datos de ejemplo para desarrollo local: roles poblados con un usuario cada uno
     * y un expediente completo (departamentos, puestos, empleados en distintos
     * estatus, solicitudes, contratos, comisiones y documentación) para poder
     * probar el sistema de RH de punta a punta sin capturar nada a mano.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $this->call(RolesPermissionsSeeder::class);
        $usuarios = $this->seedUsuarios();
        $this->seedEmpresaConfig();

        [$departamentos, $puestos] = $this->seedDepartamentosYPuestos();
        $tiposDocumento = $this->seedTiposDocumento();
        $empleados = $this->seedEmpleados($puestos, $usuarios);

        $this->seedSolicitudes($departamentos, $puestos, $usuarios);
        $this->seedContratos($empleados);
        $this->seedComisiones($empleados, $usuarios);
        $this->seedDocumentos($empleados, $tiposDocumento);
        $this->seedPeriodoNomina();

        $this->imprimirCredenciales($usuarios);
    }

    /**
     * Un usuario por rol para poder iniciar sesión y probar cada nivel de permisos.
     *
     * @return array<string, User>
     */
    private function seedUsuarios(): array
    {
        $admin = User::query()->firstOrCreate(['email' => 'test@example.com'], fn () => User::factory()->raw(['name' => 'Admin Demo', 'email' => 'test@example.com']));
        $admin->assignRole('Administrador');

        $rh = User::query()->firstOrCreate(['email' => 'rh@rh.test'], fn () => User::factory()->raw(['name' => 'Renata Herrera (RH)', 'email' => 'rh@rh.test']));
        $rh->assignRole('RH');

        $gerente = User::query()->firstOrCreate(['email' => 'gerente@rh.test'], fn () => User::factory()->raw(['name' => 'Gerardo Mendoza (Gerente)', 'email' => 'gerente@rh.test']));
        $gerente->assignRole('Gerente');

        $empleado = User::query()->firstOrCreate(['email' => 'empleado@rh.test'], fn () => User::factory()->raw(['name' => 'Elena Paredes', 'email' => 'empleado@rh.test']));
        $empleado->assignRole('Empleado');

        return compact('admin', 'rh', 'gerente', 'empleado');
    }

    private function seedEmpresaConfig(): void
    {
        $datosDemo = [
            'razon_social' => 'Talento RH Demo S.A. de C.V.',
            'nombre_comercial' => 'Talento RH',
            'rfc' => 'TRD010101AB1',
            'tamano' => 'mediana',
            'domicilio_fiscal' => 'Av. Reforma 123, Col. Juárez, Ciudad de México',
            'registro_patronal_imss' => 'A1234567890',
            'representante_legal' => 'Admin Demo',
            'escritura_constitutiva' => 'Escritura de demostración número 100, volumen 1, Notaría 10 de Ciudad de México, Lic. Notario Demo',
            'poder_representante' => 'Poder de demostración número 200, volumen 2, Notaría 10 de Ciudad de México, Lic. Notario Demo',
            'ciudad_firma' => 'Ciudad de México',
            'entidad_jurisdiccion' => 'Ciudad de México',
            'correo_privacidad' => 'privacidad@rh.test',
            'url_aviso_privacidad' => 'https://rh.test/privacidad',
            'horario_privacidad' => 'lunes a viernes de 09:00 a 18:00 horas',
        ];

        $empresa = EmpresaConfig::query()->firstOrCreate(['id' => 1], $datosDemo);

        if ($empresa->rfc === $datosDemo['rfc']) {
            $empresa->fill(array_filter($datosDemo, fn ($valor, $campo) => blank($empresa->getAttribute($campo)), ARRAY_FILTER_USE_BOTH));
            $empresa->save();
        }
    }

    /**
     * @return array{0: array<string, Departamento>, 1: array<string, Puesto>}
     */
    private function seedDepartamentosYPuestos(): array
    {
        $ventas = Departamento::query()->firstOrCreate(['nombre' => 'Ventas'], ['activo' => true]);
        $rh = Departamento::query()->firstOrCreate(['nombre' => 'Recursos Humanos'], ['activo' => true]);
        $tecnologia = Departamento::query()->firstOrCreate(['nombre' => 'Tecnología'], ['activo' => true]);
        $administracion = Departamento::query()->firstOrCreate(['nombre' => 'Administración'], ['activo' => true]);

        $puestos = [
            'ejecutivo_ventas' => Puesto::query()->firstOrCreate([
                'departamento_id' => $ventas->id,
                'nombre' => 'Ejecutivo de Ventas',
            ], [
                'descripcion' => 'Atender clientes, presentar propuestas comerciales y dar seguimiento a las ventas.',
                'activo' => true,
                'jornada' => 'diurna',
                'salario_min' => 400,
                'salario_max' => 700,
                'porcentaje_comision' => 5,
            ]),
            'gerente_ventas' => Puesto::query()->firstOrCreate([
                'departamento_id' => $ventas->id,
                'nombre' => 'Gerente de Ventas',
            ], [
                'descripcion' => 'Dirigir al equipo comercial, establecer objetivos y supervisar los resultados.',
                'activo' => true,
                'jornada' => 'diurna',
                'salario_min' => 800,
                'salario_max' => 1200,
                'porcentaje_comision' => 8,
            ]),
            'analista_rh' => Puesto::query()->firstOrCreate([
                'departamento_id' => $rh->id,
                'nombre' => 'Analista de RH',
            ], [
                'descripcion' => 'Integrar expedientes, registrar incidencias y apoyar los procesos de contratación.',
                'activo' => true,
                'jornada' => 'diurna',
                'salario_min' => 350,
                'salario_max' => 550,
                'porcentaje_comision' => 0,
            ]),
            'coordinador_rh' => Puesto::query()->firstOrCreate([
                'departamento_id' => $rh->id,
                'nombre' => 'Coordinador de RH',
            ], [
                'descripcion' => 'Coordinar contratación, capacitación y administración de personal.',
                'activo' => true,
                'jornada' => 'diurna',
                'salario_min' => 600,
                'salario_max' => 900,
                'porcentaje_comision' => 0,
            ]),
            'desarrollador' => Puesto::query()->firstOrCreate([
                'departamento_id' => $tecnologia->id,
                'nombre' => 'Desarrollador de Software',
            ], [
                'descripcion' => 'Desarrollar y mantener aplicaciones, realizar pruebas y documentar soluciones.',
                'activo' => true,
                'jornada' => 'diurna',
                'salario_min' => 500,
                'salario_max' => 900,
                'porcentaje_comision' => 0,
            ]),
            'soporte_tecnico' => Puesto::query()->firstOrCreate([
                'departamento_id' => $tecnologia->id,
                'nombre' => 'Soporte Técnico',
            ], [
                'descripcion' => 'Atender incidencias, mantener equipos y brindar soporte a los usuarios.',
                'activo' => true,
                'jornada' => 'diurna',
                'salario_min' => 350,
                'salario_max' => 550,
                'porcentaje_comision' => 0,
            ]),
            'contador' => Puesto::query()->firstOrCreate([
                'departamento_id' => $administracion->id,
                'nombre' => 'Contador',
            ], [
                'descripcion' => 'Registrar operaciones, conciliar cuentas y elaborar reportes contables.',
                'activo' => true,
                'jornada' => 'diurna',
                'salario_min' => 450,
                'salario_max' => 750,
                'porcentaje_comision' => 0,
            ]),
        ];

        return [compact('ventas', 'rh', 'tecnologia', 'administracion'), $puestos];
    }

    /**
     * @return array<string, DocumentoTipo>
     */
    private function seedTiposDocumento(): array
    {
        return [
            'ine' => DocumentoTipo::query()->firstOrCreate(['nombre' => 'INE'], ['descripcion' => 'Identificación oficial vigente.', 'obligatorio' => true, 'orden' => 1]),
            'curp' => DocumentoTipo::query()->firstOrCreate(['nombre' => 'CURP'], ['descripcion' => 'Clave Única de Registro de Población.', 'obligatorio' => true, 'orden' => 2]),
            'comprobante_domicilio' => DocumentoTipo::query()->firstOrCreate(['nombre' => 'Comprobante de domicilio'], ['descripcion' => 'No mayor a 3 meses de antigüedad.', 'obligatorio' => true, 'orden' => 3]),
            'acta_nacimiento' => DocumentoTipo::query()->firstOrCreate(['nombre' => 'Acta de nacimiento'], ['descripcion' => null, 'obligatorio' => true, 'orden' => 4]),
            'comprobante_estudios' => DocumentoTipo::query()->firstOrCreate(['nombre' => 'Comprobante de estudios'], ['descripcion' => 'Título, cédula o constancia de estudios.', 'obligatorio' => false, 'orden' => 5]),
        ];
    }

    /**
     * @param  array<string, Puesto>  $puestos
     * @param  array<string, User>  $usuarios
     * @return array<string, Empleado>
     */
    private function seedEmpleados(array $puestos, array $usuarios): array
    {
        $ana = $this->crearEmpleadoDemo('DEMO-001', [
            'numero_empleado' => 'DEMO-001',
            'puesto_id' => $puestos['ejecutivo_ventas']->id,
            'salario_diario' => $puestos['ejecutivo_ventas']->salario_min,
            'fecha_nacimiento' => '1990-05-15',
            'nombre_completo' => 'Ana',
            'apellido_paterno' => 'López',
            'apellido_materno' => 'Martínez',
            'fecha_ingreso' => now()->subYears(2),
            'estatus' => 'activo',
            'documentacion_estatus' => 'aprobada',
        ]);

        $carlos = $this->crearEmpleadoDemo('DEMO-002', [
            'numero_empleado' => 'DEMO-002',
            'puesto_id' => $puestos['analista_rh']->id,
            'salario_diario' => $puestos['analista_rh']->salario_min,
            'fecha_nacimiento' => '1990-05-15',
            'nombre_completo' => 'Carlos',
            'apellido_paterno' => 'Ramírez',
            'apellido_materno' => 'Ortiz',
            'fecha_ingreso' => now()->subDays(20),
            'estatus' => 'activo',
            'documentacion_estatus' => 'aprobada',
        ]);

        $elena = $this->crearEmpleadoDemo('DEMO-003', [
            'numero_empleado' => 'DEMO-003',
            'user_id' => $usuarios['empleado']->id,
            'puesto_id' => $puestos['desarrollador']->id,
            'salario_diario' => $puestos['desarrollador']->salario_min,
            'fecha_nacimiento' => '1990-05-15',
            'nombre_completo' => 'Elena',
            'apellido_paterno' => 'Paredes',
            'apellido_materno' => 'Vega',
            'fecha_ingreso' => now()->subMonths(8),
            'estatus' => 'activo',
            'documentacion_estatus' => 'aprobada',
        ]);

        $maria = $this->crearEmpleadoDemo('DEMO-004', [
            'numero_empleado' => 'DEMO-004',
            'puesto_id' => $puestos['contador']->id,
            'salario_diario' => $puestos['contador']->salario_min,
            'fecha_nacimiento' => '1990-05-15',
            'nombre_completo' => 'María',
            'apellido_paterno' => 'Torres',
            'apellido_materno' => 'Gómez',
            'fecha_ingreso' => now()->subDays(2),
            'estatus' => 'documentacion_pendiente',
        ]);

        $jorge = $this->crearEmpleadoDemo('DEMO-005', [
            'numero_empleado' => 'DEMO-005',
            'puesto_id' => $puestos['soporte_tecnico']->id,
            'salario_diario' => $puestos['soporte_tecnico']->salario_min,
            'fecha_nacimiento' => '1990-05-15',
            'nombre_completo' => 'Jorge',
            'apellido_paterno' => 'Sánchez',
            'apellido_materno' => 'Ruiz',
            'fecha_ingreso' => now()->subDays(5),
            'estatus' => 'documentacion_pendiente',
        ]);

        $lucia = $this->crearEmpleadoDemo('DEMO-006', [
            'numero_empleado' => 'DEMO-006',
            'puesto_id' => $puestos['coordinador_rh']->id,
            'salario_diario' => $puestos['coordinador_rh']->salario_min,
            'fecha_nacimiento' => '1990-05-15',
            'nombre_completo' => 'Lucía',
            'apellido_paterno' => 'Fernández',
            'apellido_materno' => 'Castro',
            'fecha_ingreso' => now()->subDays(10),
            'estatus' => 'documentacion_pendiente',
            'documentacion_estatus' => 'rechazada',
            'documentacion_comentario_revision' => 'El CURP no coincide con el INE, favor de volver a capturar.',
            'documentacion_revisado_por_user_id' => $usuarios['admin']->id,
            'documentacion_fecha_revision' => now()->subDay(),
        ]);

        $roberto = $this->crearEmpleadoDemo('DEMO-007', [
            'numero_empleado' => 'DEMO-007',
            'puesto_id' => $puestos['gerente_ventas']->id,
            'salario_diario' => $puestos['gerente_ventas']->salario_min,
            'fecha_nacimiento' => '1990-05-15',
            'nombre_completo' => 'Roberto',
            'apellido_paterno' => 'Díaz',
            'apellido_materno' => 'Nava',
            'fecha_ingreso' => now()->subYears(3),
            'fecha_baja' => now()->subDays(15),
            'motivo_baja' => 'Renuncia voluntaria.',
            'estatus' => 'baja',
            'documentacion_estatus' => 'aprobada',
        ]);

        return compact('ana', 'carlos', 'elena', 'maria', 'jorge', 'lucia', 'roberto');
    }

    /** @param array<string, mixed> $datos */
    private function crearEmpleadoDemo(string $numero, array $datos): Empleado
    {
        $existente = Empleado::query()->where('numero_empleado', $numero)->first();

        if ($existente !== null) {
            return $existente;
        }

        $anterior = Empleado::query()->where([
            'nombre_completo' => $datos['nombre_completo'],
            'apellido_paterno' => $datos['apellido_paterno'],
            'apellido_materno' => $datos['apellido_materno'],
            'puesto_id' => $datos['puesto_id'],
            'user_id' => $datos['user_id'] ?? null,
        ])->first();

        return $anterior ?? Empleado::factory()->create($datos);
    }

    /**
     * @param  array<string, Departamento>  $departamentos
     * @param  array<string, Puesto>  $puestos
     * @param  array<string, User>  $usuarios
     */
    private function seedSolicitudes(array $departamentos, array $puestos, array $usuarios): void
    {
        SolicitudVacante::query()->firstOrCreate(['folio' => 'SOL-2026-0001'], [
            'solicitante_user_id' => $usuarios['rh']->id,
            'departamento_id' => $departamentos['tecnologia']->id,
            'puesto_id' => $puestos['desarrollador']->id,
            'salario_propuesto' => 650,
            'numero_plazas' => 1,
            'justificacion' => 'Crecimiento del equipo de producto, se requiere reforzar el área de desarrollo.',
            'urgencia' => 'alta',
            'estatus' => 'pendiente',
        ]);

        SolicitudVacante::query()->firstOrCreate(['folio' => 'SOL-2026-0002'], [
            'solicitante_user_id' => $usuarios['gerente']->id,
            'departamento_id' => $departamentos['ventas']->id,
            'puesto_id' => $puestos['ejecutivo_ventas']->id,
            'salario_propuesto' => 500,
            'numero_plazas' => 2,
            'justificacion' => 'Apertura de nueva sucursal, se necesitan ejecutivos de ventas.',
            'urgencia' => 'media',
            'estatus' => 'aprobada',
            'revisado_por_user_id' => $usuarios['admin']->id,
            'comentario_revision' => 'Aprobado, alineado al presupuesto del trimestre.',
            'fecha_revision' => now()->subDays(3),
        ]);

        SolicitudVacante::query()->firstOrCreate(['folio' => 'SOL-2026-0003'], [
            'solicitante_user_id' => $usuarios['rh']->id,
            'departamento_id' => $departamentos['administracion']->id,
            'puesto_id' => $puestos['contador']->id,
            'salario_propuesto' => 700,
            'numero_plazas' => 1,
            'justificacion' => 'Reemplazo de baja reciente.',
            'urgencia' => 'baja',
            'estatus' => 'rechazada',
            'revisado_por_user_id' => $usuarios['admin']->id,
            'comentario_revision' => 'Se pospone hasta el siguiente trimestre por presupuesto.',
            'fecha_revision' => now()->subDay(),
        ]);
    }

    /**
     * @param  array<string, Empleado>  $empleados
     */
    private function seedContratos(array $empleados): void
    {
        $generar = new GenerarContratoPdf;
        $empresa = EmpresaConfig::current();

        foreach ($empleados as $clave => $empleado) {
            $determinado = $clave === 'carlos';
            $datos = [
                'puesto_id' => $empleado->puesto_id,
                'tipo' => $determinado ? 'determinado' : 'indeterminado',
                'duracion_meses' => $determinado ? 3 : null,
                'fecha_fin' => $determinado ? $empleado->fecha_ingreso->copy()->addMonthsNoOverflow(3)->subDay() : null,
                'fecha_celebracion' => $empleado->fecha_ingreso,
                'salario_diario' => $empleado->salario_diario,
                'salario_mensual' => number_format((float) $empleado->salario_diario * 30, 2, '.', ''),
                'jornada' => $empleado->jornada,
                'lugar_trabajo' => $empresa->domicilio_fiscal,
                'nacionalidad' => 'mexicana',
                'sexo' => in_array($clave, ['carlos', 'jorge', 'roberto'], true) ? 'HOMBRE' : 'MUJER',
                'funciones' => $empleado->puesto->descripcion ?: 'Realizar las funciones propias del puesto y elaborar informes de actividades.',
                'horario_trabajo' => 'Lunes a sábado de 09:00 a 18:00',
                'horas_semanales' => 48,
                'descanso_minutos' => 60,
                'dia_descanso' => 'domingo',
                'periodicidad_pago' => 'quincenal',
                'dias_pago' => '15 y último día de cada mes',
                'forma_pago' => 'depósito bancario',
                'beneficiarios' => [['nombre' => 'Persona Beneficiaria Demo', 'parentesco' => 'Madre', 'porcentaje' => 100]],
                'estatus' => 'borrador',
            ];
            $contrato = Contrato::query()->firstOrCreate([
                'empleado_id' => $empleado->id,
                'fecha_inicio' => $empleado->fecha_ingreso,
            ], $datos);
            $nuevo = $contrato->wasRecentlyCreated;

            if ($contrato->estatus === 'firmado') {
                continue;
            }

            if (! $nuevo) {
                $contrato->fill(array_filter($datos, fn ($valor, $campo) => blank($contrato->getAttribute($campo)), ARRAY_FILTER_USE_BOTH));
                if ($contrato->tipo === 'periodo_prueba') {
                    $contrato->fill([
                        'tipo' => 'determinado',
                        'duracion_meses' => 3,
                        'fecha_fin' => $empleado->fecha_ingreso->copy()->addMonthsNoOverflow(3)->subDay(),
                    ]);
                }
                $contrato->save();
                if ($contrato->pdf_path && Storage::disk('local')->exists($contrato->pdf_path)) {
                    continue;
                }
            }

            if (in_array($clave, ['ana', 'carlos', 'elena', 'roberto'], true)) {
                $generar->generar($contrato);
            }

            if ($nuevo && in_array($clave, ['ana', 'roberto'], true)) {
                $contrato->update(['estatus' => 'firmado', 'fecha_firma' => $empleado->fecha_ingreso]);
            }
        }
    }

    /**
     * @param  array<string, Empleado>  $empleados
     * @param  array<string, User>  $usuarios
     */
    private function seedComisiones(array $empleados, array $usuarios): void
    {
        Comision::query()->firstOrCreate(['empleado_id' => $empleados['ana']->id, 'monto_base' => 15000], [
            'porcentaje_aplicado' => 5,
            'monto_comision' => 750,
            'fecha_periodo_inicio' => now()->subMonthNoOverflow()->startOfMonth(),
            'fecha_periodo_fin' => now()->subMonthNoOverflow()->endOfMonth(),
            'estatus' => 'pagada',
            'capturado_por_user_id' => $usuarios['rh']->id,
        ]);

        Comision::query()->firstOrCreate(['empleado_id' => $empleados['ana']->id, 'monto_base' => 18500], [
            'porcentaje_aplicado' => 5,
            'monto_comision' => 925,
            'fecha_periodo_inicio' => now()->startOfMonth(),
            'fecha_periodo_fin' => now()->endOfMonth(),
            'estatus' => 'pendiente',
            'capturado_por_user_id' => $usuarios['rh']->id,
        ]);
    }

    /**
     * Sube documentos "reales" (PDF generado con dompdf) para poder probar descarga,
     * el indicador de avance y los tres escenarios: completo, parcial y vacío.
     *
     * @param  array<string, Empleado>  $empleados
     * @param  array<string, DocumentoTipo>  $tiposDocumento
     */
    private function seedDocumentos(array $empleados, array $tiposDocumento): void
    {
        $obligatorios = ['ine', 'curp', 'comprobante_domicilio', 'acta_nacimiento'];

        // Completos y ya aprobados: Ana, Carlos, Elena, Roberto.
        foreach (['ana', 'carlos', 'elena', 'roberto'] as $clave) {
            foreach ($obligatorios as $tipoClave) {
                $this->crearDocumentoDemo($empleados[$clave], $tiposDocumento[$tipoClave]);
            }
        }

        // Jorge: parcial, solo INE y CURP capturados.
        $this->crearDocumentoDemo($empleados['jorge'], $tiposDocumento['ine']);
        $this->crearDocumentoDemo($empleados['jorge'], $tiposDocumento['curp']);

        // Lucía: completo pero rechazado por RH (ver documentacion_comentario_revision).
        foreach ($obligatorios as $tipoClave) {
            $this->crearDocumentoDemo($empleados['lucia'], $tiposDocumento[$tipoClave]);
        }

        // María: sin ningún documento capturado todavía.
    }

    private function crearDocumentoDemo(Empleado $empleado, DocumentoTipo $tipo): void
    {
        if (Documento::query()->where('empleado_id', $empleado->id)->where('documento_tipo_id', $tipo->id)->exists()) {
            return;
        }

        $contenido = Pdf::loadHTML(sprintf(
            '<h2>%s</h2><p>Empleado: %s (%s)</p><p>Documento de prueba generado por el seeder de demo.</p>',
            e($tipo->nombre),
            e($empleado->nombreCompleto()),
            e($empleado->numero_empleado)
        ))->output();

        $path = 'empleados/documentos/'.$empleado->id.'/'.str($tipo->nombre)->slug().'.pdf';

        Storage::disk('local')->put($path, $contenido);

        Documento::query()->create([
            'empleado_id' => $empleado->id,
            'documento_tipo_id' => $tipo->id,
            'path' => $path,
            'nombre_original' => $tipo->nombre.'.pdf',
            'mime_type' => 'application/pdf',
            'tamano_bytes' => strlen($contenido),
        ]);
    }

    private function seedPeriodoNomina(): void
    {
        PeriodoNomina::query()->firstOrCreate([
            'tipo' => 'quincenal',
            'fecha_inicio' => now()->startOfMonth(),
        ], [
            'fecha_fin' => now()->startOfMonth()->addDays(14),
            'fecha_pago' => now()->startOfMonth()->addDays(15),
            'estatus' => 'abierto',
        ]);
    }

    /**
     * @param  array<string, User>  $usuarios
     */
    private function imprimirCredenciales(array $usuarios): void
    {
        if ($this->command === null) {
            return;
        }

        $this->command->info('Usuarios demo (contraseña inicial: password; las contraseñas existentes se conservan):');

        foreach ($usuarios as $usuario) {
            $this->command->line("  {$usuario->getRoleNames()->first()} → {$usuario->email}");
        }
    }
}
