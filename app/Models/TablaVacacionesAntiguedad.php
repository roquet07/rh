<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $anios_antiguedad
 * @property int $dias_vacaciones
 * @property Carbon $vigente_desde
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['anios_antiguedad', 'dias_vacaciones', 'vigente_desde'])]
class TablaVacacionesAntiguedad extends Model
{
    protected $table = 'tabla_vacaciones_antiguedad';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vigente_desde' => 'date',
        ];
    }
}
