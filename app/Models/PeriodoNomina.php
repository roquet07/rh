<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $tipo
 * @property Carbon $fecha_inicio
 * @property Carbon $fecha_fin
 * @property Carbon $fecha_pago
 * @property string $estatus
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['tipo', 'fecha_inicio', 'fecha_fin', 'fecha_pago', 'estatus'])]
class PeriodoNomina extends Model
{
    protected $table = 'periodos_nomina';

    /**
     * @return HasMany<NominaDetalle, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(NominaDetalle::class);
    }

    /**
     * @return HasMany<Comision, $this>
     */
    public function comisiones(): HasMany
    {
        return $this->hasMany(Comision::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'fecha_pago' => 'date',
        ];
    }
}
