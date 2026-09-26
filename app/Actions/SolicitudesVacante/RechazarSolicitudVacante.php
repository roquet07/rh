<?php

namespace App\Actions\SolicitudesVacante;

use App\Models\SolicitudVacante;
use App\Models\User;

class RechazarSolicitudVacante
{
    /**
     * Reject a vacancy request.
     */
    public function __invoke(SolicitudVacante $solicitud, User $revisor, ?string $comentario = null): SolicitudVacante
    {
        $solicitud->update([
            'estatus' => 'rechazada',
            'revisado_por_user_id' => $revisor->id,
            'comentario_revision' => $comentario,
            'fecha_revision' => now(),
        ]);

        return $solicitud;
    }
}
