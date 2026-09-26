<?php

namespace App\Actions\Empleados;

use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Empleado;
use App\Models\User;
use App\Support\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubirDocumentoEmpleado
{
    private const EXTENSIONES_IMAGEN = ['jpg', 'jpeg', 'png'];

    /**
     * Sube un documento para el empleado y tipo dados. Si ya existía uno para ese mismo
     * tipo, reemplaza el archivo anterior (se borra del disco) en vez de acumular versiones.
     */
    public function __invoke(Empleado $empleado, DocumentoTipo $documentoTipo, UploadedFile $archivo, User $subidoPor): Documento
    {
        $anterior = Documento::query()
            ->where('empleado_id', $empleado->id)
            ->where('documento_tipo_id', $documentoTipo->id)
            ->first();

        if ($anterior !== null) {
            Storage::disk('local')->delete($anterior->path);
        }

        $esImagen = in_array(strtolower((string) $archivo->getClientOriginalExtension()), self::EXTENSIONES_IMAGEN, true);

        if ($esImagen) {
            [$contenido, $mime] = ImageCompressor::comprimir($archivo);
            $path = 'empleados/documentos/'.$empleado->id.'/'.Str::uuid().'.jpg';
            Storage::disk('local')->put($path, $contenido);
            $tamano = strlen($contenido);
        } else {
            $path = $archivo->store('empleados/documentos/'.$empleado->id, 'local');
            $mime = $archivo->getMimeType() ?? 'application/pdf';
            $tamano = $archivo->getSize() ?: 0;
        }

        return Documento::query()->updateOrCreate(
            ['empleado_id' => $empleado->id, 'documento_tipo_id' => $documentoTipo->id],
            [
                'subido_por_user_id' => $subidoPor->id,
                'path' => $path,
                'nombre_original' => $archivo->getClientOriginalName(),
                'mime_type' => $mime,
                'tamano_bytes' => $tamano,
            ]
        );
    }
}
