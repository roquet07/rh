<?php

namespace App\Support;

use App\Models\Empleado;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class EmpleadoRules
{
    public const RFC_REGEX = '/^[A-ZÑ&]{4}\d{6}[A-Z\d]{3}$/';

    public const CURP_REGEX = '/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z\d]\d$/';

    public const NSS_REGEX = '/^\d{11}$/';

    public const CP_REGEX = '/^\d{5}$/';

    public const TELEFONO_REGEX = '/^\d{10}$/';

    /**
     * Get the validation rules used to validate an empleado.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public static function rules(?int $empleadoId = null): array
    {
        return [
            // Datos personales
            'nombre_completo' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date', 'before:-18 years'],
            'estado_civil' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'regex:'.self::TELEFONO_REGEX],
            'correo_personal' => ['nullable', 'email', 'max:255'],

            // Identificación
            'rfc' => ['required', 'regex:'.self::RFC_REGEX, Rule::unique(Empleado::class)->ignore($empleadoId)],
            'curp' => ['required', 'regex:'.self::CURP_REGEX, Rule::unique(Empleado::class)->ignore($empleadoId)],
            'nss' => ['nullable', 'regex:'.self::NSS_REGEX],
            'regimen_fiscal' => ['nullable', 'string', Rule::in(['Sueldos y salarios', 'Asimilados a salarios'])],

            // Domicilio
            'domicilio' => ['required', 'string', 'max:255'],
            'colonia' => ['required', 'string', 'max:255'],
            'codigo_postal' => ['required', 'regex:'.self::CP_REGEX],
            'municipio' => ['nullable', 'string', 'max:255'],
            'estado_direccion' => ['required', 'string', 'max:255'],

            // Datos laborales
            'puesto_id' => ['required', 'integer', 'exists:puestos,id'],
            'jefe_directo_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'fecha_ingreso' => ['required', 'date'],
            'salario_diario' => ['required', 'numeric', 'min:0'],
            'jornada' => ['required', 'string', Rule::in(['diurna', 'nocturna', 'mixta'])],
        ];
    }

    /**
     * Reglas adicionales de campos que solo se gestionan desde la edición del expediente
     * (no forman parte del alta por pasos): datos bancarios y contacto de emergencia.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public static function reglasAdicionalesEdicion(): array
    {
        return [
            'sucursal' => ['nullable', 'string', 'max:255'],
            'banco' => ['nullable', 'string', 'max:255'],
            'clabe' => ['nullable', 'string', 'max:18'],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:255'],
            'contacto_emergencia_telefono' => ['nullable', 'regex:'.self::TELEFONO_REGEX],
        ];
    }
}
