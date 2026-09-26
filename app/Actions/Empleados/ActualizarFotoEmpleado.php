<?php

namespace App\Actions\Empleados;

use App\Models\Empleado;
use App\Support\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ActualizarFotoEmpleado
{
    private const MAX_DIMENSION = 800;

    private const JPEG_QUALITY = 80;

    /**
     * Comprime y guarda la foto del empleado, reemplazando la anterior si existía.
     */
    public function __invoke(Empleado $empleado, UploadedFile $archivo): Empleado
    {
        if ($empleado->foto_path !== null) {
            Storage::disk('local')->delete($empleado->foto_path);
        }

        [$contenido] = ImageCompressor::comprimir($archivo, self::MAX_DIMENSION, self::JPEG_QUALITY);
        $path = 'empleados/fotos/'.$empleado->id.'.jpg';

        Storage::disk('local')->put($path, $contenido);

        $empleado->update(['foto_path' => $path]);

        return $empleado;
    }
}
