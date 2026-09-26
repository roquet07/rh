<?php

namespace App\Actions\Empleados;

use App\Models\Empleado;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MostrarFotoEmpleado
{
    public function __invoke(Empleado $empleado): StreamedResponse
    {
        abort_unless($empleado->foto_path !== null, 404);

        return Storage::disk('local')->response($empleado->foto_path, headers: [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
