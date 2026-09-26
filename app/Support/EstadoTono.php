<?php

namespace App\Support;

class EstadoTono
{
    /**
     * @var array<string, string>
     */
    private const MAPA = [
        // Empleados
        'activo' => 'success',
        'periodo_prueba' => 'warning',
        'documentacion_pendiente' => 'warning',
        'baja' => 'danger',

        // Solicitudes de vacante
        'pendiente' => 'warning',
        'aprobada' => 'success',
        'rechazada' => 'danger',

        // Contratos
        'borrador' => 'neutral',
        'generado' => 'info',
        'firmado' => 'success',

        // Comisiones
        'pagada' => 'success',

        // Periodos de nómina
        'abierto' => 'info',
        'calculado' => 'warning',
        'pagado' => 'success',
        'cerrado' => 'neutral',
    ];

    /**
     * Devuelve el tono (success|warning|danger|info|neutral) para un estatus dado.
     */
    public static function tono(string $estado): string
    {
        return self::MAPA[$estado] ?? 'neutral';
    }

    /**
     * Etiqueta legible en español para un estatus dado.
     */
    public static function etiqueta(string $estado): string
    {
        return match ($estado) {
            'activo' => 'Activo',
            'periodo_prueba' => 'Periodo de prueba',
            'documentacion_pendiente' => 'Documentación pendiente',
            'baja' => 'Baja',
            'pendiente' => 'Pendiente',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
            'borrador' => 'Borrador',
            'generado' => 'Generado',
            'firmado' => 'Firmado',
            'pagada' => 'Pagada',
            'abierto' => 'Abierto',
            'calculado' => 'Calculado',
            'pagado' => 'Pagado',
            'cerrado' => 'Cerrado',
            default => ucfirst(str_replace('_', ' ', $estado)),
        };
    }
}
