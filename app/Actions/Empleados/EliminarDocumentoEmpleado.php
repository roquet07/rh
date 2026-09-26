<?php

namespace App\Actions\Empleados;

use App\Models\Documento;
use Illuminate\Support\Facades\Storage;

class EliminarDocumentoEmpleado
{
    public function __invoke(Documento $documento): void
    {
        Storage::disk('local')->delete($documento->path);
        $documento->delete();
    }
}
