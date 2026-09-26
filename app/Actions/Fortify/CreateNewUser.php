<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\EmpresaConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public const RFC_PERSONA_MORAL_REGEX = '/^[A-ZÑ&]{3}\d{6}[A-Z\d]{3}$/';

    /**
     * Validate and create a newly registered user.
     *
     * Además de la cuenta de usuario, el primer registro del sistema (cuando aún no existe una
     * empresa configurada) siembra la configuración de empresa (singleton, ver EmpresaConfig) con
     * los datos del paso 2 del registro y asigna el rol Administrador a quien la registró. Este
     * sistema es de una sola empresa: registros posteriores no vuelven a sobrescribir esos datos.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $esPrimeraEmpresa = ! EmpresaConfig::query()->exists();

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'empresa_nombre' => [$esPrimeraEmpresa ? 'required' : 'nullable', 'string', 'max:255'],
            'empresa_rfc' => [$esPrimeraEmpresa ? 'required' : 'nullable', 'regex:'.self::RFC_PERSONA_MORAL_REGEX],
            'empresa_tamano' => [$esPrimeraEmpresa ? 'required' : 'nullable', Rule::in(['1 a 10', '11 a 50', '51 a 200', 'Más de 200'])],
        ])->validate();

        return DB::transaction(function () use ($input, $esPrimeraEmpresa) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            if ($esPrimeraEmpresa) {
                EmpresaConfig::query()->create([
                    'razon_social' => $input['empresa_nombre'],
                    'rfc' => $input['empresa_rfc'],
                    'tamano' => $input['empresa_tamano'],
                    'domicilio_fiscal' => '',
                ]);

                $user->assignRole('Administrador');
            }

            return $user;
        });
    }
}
