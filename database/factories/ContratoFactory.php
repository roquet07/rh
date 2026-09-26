<?php

namespace Database\Factories;

use App\Models\Contrato;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Contrato> */
class ContratoFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'empleado_id' => Empleado::factory(),
            'puesto_id' => fn (array $attributes) => Empleado::query()->whereKey($attributes['empleado_id'])->firstOrFail()->puesto_id,
            'tipo' => 'indeterminado',
            'fecha_inicio' => '2026-10-01',
            'fecha_celebracion' => '2026-09-26',
            'salario_diario' => '666.67',
            'salario_mensual' => '20000.00',
            'jornada' => 'diurna',
            'lugar_trabajo' => 'Oficina de prueba',
            'nacionalidad' => 'mexicana',
            'sexo' => 'MUJER',
            'funciones' => 'Coordinar al equipo y elaborar informes de resultados.',
            'horario_trabajo' => 'Lunes a sábado de 09:00 a 18:00',
            'horas_semanales' => 48,
            'descanso_minutos' => 60,
            'dia_descanso' => 'domingo',
            'periodicidad_pago' => 'quincenal',
            'dias_pago' => '15 y último día de cada mes',
            'forma_pago' => 'depósito bancario',
            'beneficiarios' => [['nombre' => 'Persona Beneficiaria', 'parentesco' => 'Madre', 'porcentaje' => 100]],
            'estatus' => 'borrador',
        ];
    }

    public function determinado(): static
    {
        return $this->state(fn () => [
            'tipo' => 'determinado',
            'duracion_meses' => 3,
            'fecha_fin' => '2026-12-31',
        ]);
    }
}
