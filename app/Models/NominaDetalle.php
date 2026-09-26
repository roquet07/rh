<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $periodo_nomina_id
 * @property int $empleado_id
 * @property float $dias_trabajados
 * @property float $salario_diario
 * @property float $sdi
 * @property float $percepcion_salario
 * @property float $percepcion_comision
 * @property float $percepcion_aguinaldo
 * @property float $percepcion_ptu
 * @property float $percepcion_otras
 * @property float $deduccion_isr
 * @property float $deduccion_imss
 * @property float $deduccion_otras
 * @property float $total_percepciones
 * @property float $total_deducciones
 * @property float $neto_pagar
 * @property string|null $recibo_pdf_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'periodo_nomina_id',
    'empleado_id',
    'dias_trabajados',
    'salario_diario',
    'sdi',
    'percepcion_salario',
    'percepcion_comision',
    'percepcion_aguinaldo',
    'percepcion_ptu',
    'percepcion_otras',
    'deduccion_isr',
    'deduccion_imss',
    'deduccion_otras',
    'total_percepciones',
    'total_deducciones',
    'neto_pagar',
    'recibo_pdf_path',
])]
class NominaDetalle extends Model
{
    /**
     * @return BelongsTo<PeriodoNomina, $this>
     */
    public function periodoNomina(): BelongsTo
    {
        return $this->belongsTo(PeriodoNomina::class);
    }

    /**
     * @return BelongsTo<Empleado, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dias_trabajados' => 'decimal:1',
            'salario_diario' => 'decimal:2',
            'sdi' => 'decimal:2',
            'percepcion_salario' => 'decimal:2',
            'percepcion_comision' => 'decimal:2',
            'percepcion_aguinaldo' => 'decimal:2',
            'percepcion_ptu' => 'decimal:2',
            'percepcion_otras' => 'decimal:2',
            'deduccion_isr' => 'decimal:2',
            'deduccion_imss' => 'decimal:2',
            'deduccion_otras' => 'decimal:2',
            'total_percepciones' => 'decimal:2',
            'total_deducciones' => 'decimal:2',
            'neto_pagar' => 'decimal:2',
        ];
    }
}
