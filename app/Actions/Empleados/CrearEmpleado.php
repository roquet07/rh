<?php

namespace App\Actions\Empleados;

use App\Models\Empleado;
use Illuminate\Support\Facades\DB;

class CrearEmpleado
{
    /**
     * Create a new empleado, assigning the next sequential numero_empleado.
     *
     * @param  array<string, mixed>  $datos
     */
    public function __invoke(array $datos): Empleado
    {
        return DB::transaction(function () use ($datos) {
            $siguiente = ((int) (Empleado::query()->max('id') ?? 0)) + 1;

            return Empleado::query()->create($datos + [
                'numero_empleado' => 'EMP-'.str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT),
                'estatus' => 'documentacion_pendiente',
            ]);
        });
    }
}
