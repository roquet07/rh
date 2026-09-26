<?php

namespace App\Actions\Empleados;

use App\Models\Empleado;
use App\Models\User;

class RevisarDocumentacionEmpleado
{
    public function aprobar(Empleado $empleado, User $revisor): Empleado
    {
        $empleado->update([
            'documentacion_estatus' => 'aprobada',
            'documentacion_revisado_por_user_id' => $revisor->id,
            'documentacion_fecha_revision' => now(),
            'documentacion_comentario_revision' => null,
            'estatus' => 'activo',
        ]);

        return $empleado;
    }

    public function rechazar(Empleado $empleado, User $revisor, string $comentario): Empleado
    {
        $empleado->update([
            'documentacion_estatus' => 'rechazada',
            'documentacion_revisado_por_user_id' => $revisor->id,
            'documentacion_fecha_revision' => now(),
            'documentacion_comentario_revision' => $comentario,
        ]);

        return $empleado;
    }
}
