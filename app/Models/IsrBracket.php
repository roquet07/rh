<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ejercicio_fiscal
 * @property string $periodicidad
 * @property float $limite_inferior
 * @property float|null $limite_superior
 * @property float $cuota_fija
 * @property float $porcentaje_excedente
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'ejercicio_fiscal',
    'periodicidad',
    'limite_inferior',
    'limite_superior',
    'cuota_fija',
    'porcentaje_excedente',
])]
class IsrBracket extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'limite_inferior' => 'decimal:2',
            'limite_superior' => 'decimal:2',
            'cuota_fija' => 'decimal:2',
            'porcentaje_excedente' => 'decimal:2',
        ];
    }
}
