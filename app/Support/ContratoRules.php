<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\ValidationException;

class ContratoRules
{
    /** @return array<string, array<int, string|In>> */
    public static function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['indeterminado', 'determinado'])],
            'fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'duracion_meses' => ['exclude_unless:tipo,determinado', 'required', 'integer', 'min:1', 'max:120'],
            'fecha_celebracion' => ['required', 'date_format:Y-m-d'],
            'salario_diario' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'salario_mensual' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'jornada' => ['required', Rule::in(['diurna', 'nocturna', 'mixta'])],
            'lugar_trabajo' => ['required', 'string', 'max:255'],
            'nacionalidad' => ['required', 'string', 'max:100'],
            'sexo' => ['required', 'string', 'max:50'],
            'funciones' => ['required', 'string', 'max:20000'],
            'horario_trabajo' => ['required', 'string', 'max:255'],
            'horas_semanales' => ['required', 'integer', 'between:1,48'],
            'descanso_minutos' => ['required', 'integer', 'between:30,180'],
            'dia_descanso' => ['required', 'string', 'max:100'],
            'periodicidad_pago' => ['required', Rule::in(['semanal', 'quincenal'])],
            'dias_pago' => ['required', 'string', 'max:255'],
            'forma_pago' => ['required', 'string', 'max:255'],
            'beneficiarios' => ['required', 'array', 'min:1', 'max:10'],
            'beneficiarios.*' => ['required', 'array:nombre,parentesco,porcentaje'],
            'beneficiarios.*.nombre' => ['required', 'string', 'max:255'],
            'beneficiarios.*.parentesco' => ['required', 'string', 'max:100'],
            'beneficiarios.*.porcentaje' => ['required', 'numeric', 'gt:0', 'max:100', 'decimal:0,2'],
            'clausulas_adicionales' => ['nullable', 'string', 'max:20000'],
        ];
    }

    /** @param array<string, mixed> $datos */
    public static function validarCondiciones(array $datos): void
    {
        $total = 0;
        foreach ($datos['beneficiarios'] as $beneficiario) {
            $total += (int) round((float) $beneficiario['porcentaje'] * 100);
        }

        if ($total !== 10000) {
            throw ValidationException::withMessages(['beneficiarios' => 'Los porcentajes de los beneficiarios deben sumar 100%.']);
        }

        $maximo = match ($datos['jornada']) {
            'nocturna' => 42,
            'mixta' => 45,
            default => 48,
        };

        if ((int) $datos['horas_semanales'] > $maximo) {
            throw ValidationException::withMessages(['horas_semanales' => "La jornada seleccionada admite hasta {$maximo} horas semanales."]);
        }

        if (abs(round((float) $datos['salario_mensual'] / 30, 2) - (float) $datos['salario_diario']) > 0.001) {
            throw ValidationException::withMessages(['salario_mensual' => 'El salario diario debe corresponder al salario mensual dividido entre 30, redondeado a dos decimales.']);
        }
    }

    /** @return array<string, string> */
    public static function attributes(): array
    {
        return [
            'tipo' => 'tipo de contrato',
            'fecha_inicio' => 'fecha de inicio',
            'duracion_meses' => 'duración en meses',
            'fecha_celebracion' => 'fecha de celebración',
            'salario_diario' => 'salario diario',
            'salario_mensual' => 'salario mensual',
            'lugar_trabajo' => 'lugar de trabajo',
            'horario_trabajo' => 'horario de trabajo',
            'horas_semanales' => 'horas semanales',
            'descanso_minutos' => 'descanso para alimentos',
            'dia_descanso' => 'día de descanso',
            'periodicidad_pago' => 'periodicidad de pago',
            'dias_pago' => 'días de pago',
            'forma_pago' => 'forma de pago',
            'beneficiarios.*.nombre' => 'nombre del beneficiario',
            'beneficiarios.*.parentesco' => 'parentesco del beneficiario',
            'beneficiarios.*.porcentaje' => 'porcentaje del beneficiario',
        ];
    }
}
