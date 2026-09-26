<?php

namespace App\Actions\Empleados;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportarEmpleadosCsv
{
    /**
     * Exporta a CSV la lista de empleados respetando los filtros de búsqueda, estado y departamento.
     */
    public function __invoke(Request $request): StreamedResponse
    {
        $buscar = (string) $request->query('buscar', '');
        $estado = (string) $request->query('estado', 'Todos');
        $departamentoId = (string) $request->query('departamento_id', '');

        $empleados = Empleado::query()
            ->with('puesto.departamento')
            ->when($buscar !== '', fn ($q) => $q->where(function ($q) use ($buscar) {
                $q->where('nombre_completo', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('numero_empleado', 'like', "%{$buscar}%")
                    ->orWhere('rfc', 'like', "%{$buscar}%");
            }))
            ->when($departamentoId !== '', fn ($q) => $q->whereHas('puesto', fn ($q) => $q->where('departamento_id', $departamentoId)))
            ->when($estado === 'Activo', fn ($q) => $q->where('estatus', 'activo'))
            ->when($estado === 'Baja', fn ($q) => $q->where('estatus', 'baja'))
            ->orderBy('nombre_completo')
            ->get();

        $encabezados = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="empleados.csv"',
        ];

        return response()->streamDownload(function () use ($empleados) {
            $salida = fopen('php://output', 'w');

            if ($salida === false) {
                return;
            }

            fwrite($salida, "\xEF\xBB\xBF");

            fputcsv($salida, ['Número', 'RFC', 'Nombre completo', 'Puesto', 'Departamento', 'Fecha de ingreso', 'Estatus']);

            foreach ($empleados as $empleado) {
                fputcsv($salida, [
                    $empleado->numero_empleado,
                    $empleado->rfc,
                    $empleado->nombreCompleto(),
                    $empleado->puesto->nombre,
                    $empleado->puesto->departamento->nombre,
                    $empleado->fecha_ingreso->format('Y-m-d'),
                    $empleado->estatus,
                ]);
            }

            fclose($salida);
        }, 'empleados.csv', $encabezados);
    }
}
