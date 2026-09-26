<?php

namespace App\Support;

class Breadcrumbs
{
    /**
     * @var array<string, array<int, string>>
     */
    private const RUTAS = [
        'dashboard' => ['Inicio', 'Dashboard'],

        'empleados.index' => ['Recursos Humanos', 'Empleados'],
        'empleados.create' => ['Recursos Humanos', 'Empleados', 'Nuevo empleado'],
        'empleados.edit' => ['Recursos Humanos', 'Empleados', 'Editar'],
        'empleados.show' => ['Recursos Humanos', 'Empleados', 'Expediente'],
        'mi-expediente' => ['Recursos Humanos', 'Mi expediente'],

        'puestos.index' => ['Recursos Humanos', 'Puestos'],
        'documento-tipos.index' => ['Recursos Humanos', 'Tipos de documento'],
        'departamentos.index' => ['Recursos Humanos', 'Departamentos'],

        'solicitudes.index' => ['Recursos Humanos', 'Solicitudes de vacante'],
        'solicitudes.create' => ['Recursos Humanos', 'Solicitudes de vacante', 'Nueva solicitud'],
        'solicitudes.show' => ['Recursos Humanos', 'Solicitudes de vacante', 'Detalle'],

        'contratos.index' => ['Recursos Humanos', 'Contratos'],
        'contratos.create' => ['Recursos Humanos', 'Contratos', 'Nuevo contrato'],
        'contratos.show' => ['Recursos Humanos', 'Contratos', 'Detalle'],

        'comisiones.index' => ['Recursos Humanos', 'Comisiones'],

        'nomina.periodos' => ['Recursos Humanos', 'Nómina'],
        'nomina.periodo-detalle' => ['Recursos Humanos', 'Nómina', 'Periodo'],

        'empresa.config' => ['Recursos Humanos', 'Config. empresa'],
        'admin.usuarios' => ['Recursos Humanos', 'Usuarios'],
        'admin.roles' => ['Recursos Humanos', 'Roles'],
        'config-fiscal.isr' => ['Recursos Humanos', 'Config. fiscal', 'ISR'],
        'config-fiscal.imss' => ['Recursos Humanos', 'Config. fiscal', 'IMSS'],
        'config-fiscal.vacaciones' => ['Recursos Humanos', 'Config. fiscal', 'Vacaciones'],

        'profile.edit' => ['Configuración', 'Perfil'],
        'appearance.edit' => ['Configuración', 'Apariencia'],
        'security.edit' => ['Configuración', 'Seguridad'],
    ];

    /**
     * @return array<int, string>
     */
    public static function forRoute(?string $name): array
    {
        return self::RUTAS[$name] ?? ['Talento RH'];
    }
}
