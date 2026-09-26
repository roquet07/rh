<?php

namespace Database\Factories;

use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Documento>
 */
class DocumentoFactory extends Factory
{
    protected $model = Documento::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empleado_id' => Empleado::factory(),
            'documento_tipo_id' => DocumentoTipo::factory(),
            'path' => 'empleados/documentos/test/documento.pdf',
            'nombre_original' => 'documento.pdf',
            'mime_type' => 'application/pdf',
            'tamano_bytes' => 1024,
        ];
    }
}
