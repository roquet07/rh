<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ejercicio_fiscal
 * @property float $uma_diaria
 * @property float $porcentaje_obrero
 * @property float $porcentaje_patronal
 * @property int $tope_sbc_umas
 * @property string|null $notas
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'ejercicio_fiscal',
    'uma_diaria',
    'porcentaje_obrero',
    'porcentaje_patronal',
    'tope_sbc_umas',
    'notas',
])]
class ImssRate extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'uma_diaria' => 'decimal:2',
            'porcentaje_obrero' => 'decimal:4',
            'porcentaje_patronal' => 'decimal:4',
        ];
    }
}
