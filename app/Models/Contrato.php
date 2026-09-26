<?php

namespace App\Models;

use Database\Factories\ContratoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int|null $duracion_meses
 * @property Carbon|null $fecha_celebracion
 * @property string|null $nacionalidad
 * @property string|null $sexo
 * @property string|null $funciones
 * @property string|null $horario_trabajo
 * @property int|null $horas_semanales
 * @property int|null $descanso_minutos
 * @property string|null $dia_descanso
 * @property string|null $salario_mensual
 * @property string|null $periodicidad_pago
 * @property string|null $dias_pago
 * @property string|null $forma_pago
 * @property array<int, array{nombre: string, parentesco: string, porcentaje: float}>|null $beneficiarios
 * @property int $id
 * @property int $empleado_id
 * @property int|null $solicitud_vacante_id
 * @property int $puesto_id
 * @property string $tipo
 * @property Carbon $fecha_inicio
 * @property Carbon|null $fecha_fin
 * @property float $salario_diario
 * @property string $jornada
 * @property string $lugar_trabajo
 * @property string|null $clausulas_adicionales
 * @property string $estatus
 * @property Carbon|null $fecha_firma
 * @property string|null $documento_firmado_path
 * @property string|null $pdf_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'duracion_meses',
    'fecha_celebracion',
    'nacionalidad',
    'sexo',
    'funciones',
    'horario_trabajo',
    'horas_semanales',
    'descanso_minutos',
    'dia_descanso',
    'salario_mensual',
    'periodicidad_pago',
    'dias_pago',
    'forma_pago',
    'beneficiarios',

    'empleado_id',
    'solicitud_vacante_id',
    'puesto_id',
    'tipo',
    'fecha_inicio',
    'fecha_fin',
    'salario_diario',
    'jornada',
    'lugar_trabajo',
    'clausulas_adicionales',
    'estatus',
    'fecha_firma',
    'documento_firmado_path',
    'pdf_path',
])]
class Contrato extends Model
{
    /** @use HasFactory<ContratoFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Contrato $contrato): void {
            if ($contrato->tipo === 'indeterminado') {
                $contrato->fecha_fin = null;
                $contrato->duracion_meses = null;
            }
        });
    }

    /**
     * @return BelongsTo<Empleado, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * @return BelongsTo<Puesto, $this>
     */
    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }

    /**
     * @return BelongsTo<SolicitudVacante, $this>
     */
    public function solicitudVacante(): BelongsTo
    {
        return $this->belongsTo(SolicitudVacante::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_celebracion' => 'date',
            'duracion_meses' => 'integer',
            'horas_semanales' => 'integer',
            'descanso_minutos' => 'integer',
            'salario_mensual' => 'decimal:2',
            'beneficiarios' => 'array',
            'fecha_fin' => 'date',
            'fecha_firma' => 'date',
            'salario_diario' => 'decimal:2',
        ];
    }
}
