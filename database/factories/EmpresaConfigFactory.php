<?php

namespace Database\Factories;

use App\Models\EmpresaConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EmpresaConfig> */
class EmpresaConfigFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'razon_social' => 'Empresa de Prueba S.A. de C.V.',
            'rfc' => 'EPR010101AB1',
            'domicilio_fiscal' => 'Avenida de Ejemplo 123, Guadalajara, Jalisco',
            'representante_legal' => 'Representante de Prueba',
            'escritura_constitutiva' => 'Escritura número 123, volumen 4, ante la Notaría 5 de Guadalajara, a cargo de la Lic. Persona Notaria',
            'poder_representante' => 'Escritura número 456, volumen 7, ante la Notaría 5 de Guadalajara, a cargo de la Lic. Persona Notaria',
            'ciudad_firma' => 'Guadalajara',
            'entidad_jurisdiccion' => 'Jalisco',
            'correo_privacidad' => 'privacidad@example.test',
            'url_aviso_privacidad' => 'https://example.test/privacidad',
            'horario_privacidad' => 'lunes a viernes de 09:00 a 18:00 horas',
        ];
    }
}
