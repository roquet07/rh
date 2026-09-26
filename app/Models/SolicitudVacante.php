<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $folio
 * @property int $solicitante_user_id
 * @property int $departamento_id
 * @property int|null $puesto_id
 * @property string|null $puesto_propuesto
 * @property float $salario_propuesto
 * @property int $numero_plazas
 * @property string $justificacion
 * @property string $urgencia
 * @property string $estatus
 * @property int|null $revisado_por_user_id
 * @property string|null $comentario_revision
 * @property Carbon|null $fecha_revision
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'folio',
    'solicitante_user_id',
    'departamento_id',
    'puesto_id',
    'puesto_propuesto',
    'salario_propuesto',
    'numero_plazas',
    'justificacion',
    'urgencia',
    'estatus',
    'revisado_por_user_id',
    'comentario_revision',
    'fecha_revision',
])]
class SolicitudVacante extends Model
{
    protected $table = 'solicitudes_vacante';

    /**
     * @return BelongsTo<User, $this>
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por_user_id');
    }

    /**
     * @return BelongsTo<Departamento, $this>
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    /**
     * @return BelongsTo<Puesto, $this>
     */
    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }

    /**
     * @return HasMany<Empleado, $this>
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'salario_propuesto' => 'decimal:2',
            'fecha_revision' => 'datetime',
        ];
    }
}
