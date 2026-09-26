<?php

namespace App\Actions\SolicitudesVacante;

use App\Models\SolicitudVacante;
use App\Models\User;

class AprobarSolicitudVacante
{
    /**
     * Approve a vacancy request.
     */
    public function __invoke(SolicitudVacante $solicitud, User $revisor, ?string $comentario = null): SolicitudVacante
    {
        $solicitud->update([
            'estatus' => 'aprobada',
            'revisado_por_user_id' => $revisor->id,
            'comentario_revision' => $comentario,
            'fecha_revision' => now(),
        ]);

        return $solicitud;
    }
}
