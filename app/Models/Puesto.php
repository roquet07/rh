<?php

namespace App\Models;

use Database\Factories\PuestoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $departamento_id
 * @property string $nombre
 * @property string|null $descripcion
 * @property float $salario_min
 * @property float $salario_max
 * @property float $porcentaje_comision
 * @property string $jornada
 * @property bool $activo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'departamento_id',
    'nombre',
    'descripcion',
    'salario_min',
    'salario_max',
    'porcentaje_comision',
    'jornada',
    'activo',
])]
class Puesto extends Model
{
    /** @use HasFactory<PuestoFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Departamento, $this>
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
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
            'salario_min' => 'decimal:2',
            'salario_max' => 'decimal:2',
            'porcentaje_comision' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
