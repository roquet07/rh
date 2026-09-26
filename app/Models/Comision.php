<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $empleado_id
 * @property int|null $periodo_nomina_id
 * @property float $monto_base
 * @property float $porcentaje_aplicado
 * @property float $monto_comision
 * @property Carbon $fecha_periodo_inicio
 * @property Carbon $fecha_periodo_fin
 * @property string $estatus
 * @property int $capturado_por_user_id
 * @property string|null $notas
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'empleado_id',
    'periodo_nomina_id',
    'monto_base',
    'porcentaje_aplicado',
    'monto_comision',
    'fecha_periodo_inicio',
    'fecha_periodo_fin',
    'estatus',
    'capturado_por_user_id',
    'notas',
])]
class Comision extends Model
{
    protected $table = 'comisiones';

    /**
     * @return BelongsTo<Empleado, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * @return BelongsTo<PeriodoNomina, $this>
     */
    public function periodoNomina(): BelongsTo
    {
        return $this->belongsTo(PeriodoNomina::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function capturadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'capturado_por_user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto_base' => 'decimal:2',
            'porcentaje_aplicado' => 'decimal:2',
            'monto_comision' => 'decimal:2',
            'fecha_periodo_inicio' => 'date',
            'fecha_periodo_fin' => 'date',
        ];
    }
}
