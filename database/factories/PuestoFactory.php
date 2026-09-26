<?php

namespace Database\Factories;

use App\Models\Departamento;
use App\Models\Puesto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Puesto>
 */
class PuestoFactory extends Factory
{
    protected $model = Puesto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = $this->faker->numberBetween(300, 600);

        return [
            'departamento_id' => Departamento::factory(),
            'nombre' => ucfirst($this->faker->unique()->jobTitle()),
            'descripcion' => $this->faker->sentence(),
            'salario_min' => $min,
            'salario_max' => $min + $this->faker->numberBetween(100, 400),
            'porcentaje_comision' => 0,
            'jornada' => 'diurna',
            'activo' => true,
        ];
    }
}
