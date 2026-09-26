<?php

namespace Database\Factories;

use App\Models\Empleado;
use App\Models\Puesto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empleado>
 */
class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $curp = strtoupper($this->faker->bothify('????######')).
            $this->faker->randomElement(['H', 'M']).
            strtoupper($this->faker->bothify('?????')).
            strtoupper($this->faker->bothify('?')).
            $this->faker->numerify('#');

        return [
            'puesto_id' => Puesto::factory(),
            'numero_empleado' => 'EMP-'.$this->faker->unique()->numerify('####'),
            'nombre_completo' => $this->faker->firstName(),
            'apellido_paterno' => $this->faker->lastName(),
            'apellido_materno' => $this->faker->lastName(),
            'rfc' => strtoupper($this->faker->unique()->bothify('????######???')),
            'curp' => $this->faker->unique()->passthrough($curp),
            'nss' => $this->faker->numerify('###########'),
            'regimen_fiscal' => 'Sueldos y salarios',
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-55 years', '-18 years'),
            'estado_civil' => $this->faker->randomElement(['Soltero(a)', 'Casado(a)', 'Divorciado(a)']),
            'domicilio' => $this->faker->streetAddress(),
            'colonia' => $this->faker->citySuffix(),
            'codigo_postal' => $this->faker->numerify('#####'),
            'municipio' => $this->faker->city(),
            'estado_direccion' => $this->faker->randomElement(['Ciudad de México', 'Jalisco', 'Nuevo León']),
            'telefono' => $this->faker->numerify('##########'),
            'correo_personal' => $this->faker->safeEmail(),
            'fecha_ingreso' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'salario_diario' => $this->faker->randomFloat(2, 300, 900),
            'jornada' => 'diurna',
            'banco' => $this->faker->randomElement(['BBVA', 'Santander', 'Banorte']),
            'clabe' => $this->faker->numerify('####################'),
            'estatus' => 'activo',
        ];
    }
}
