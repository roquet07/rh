<?php

namespace App\Actions\Empleados;

use App\Models\Empleado;
use Illuminate\Support\Facades\Storage;

class EliminarFotoEmpleado
{
    public function __invoke(Empleado $empleado): void
    {
        if ($empleado->foto_path === null) {
            return;
        }

        Storage::disk('local')->delete($empleado->foto_path);

        $empleado->update(['foto_path' => null]);
    }
}
