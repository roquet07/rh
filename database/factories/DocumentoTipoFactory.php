<?php

namespace Database\Factories;

use App\Models\DocumentoTipo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentoTipo>
 */
class DocumentoTipoFactory extends Factory
{
    protected $model = DocumentoTipo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => ucfirst($this->faker->unique()->word()).' '.$this->faker->word(),
            'descripcion' => $this->faker->sentence(),
            'obligatorio' => true,
            'activo' => true,
            'orden' => 0,
        ];
    }
}
