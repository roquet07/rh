<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\EmpleadoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int $puesto_id
 * @property int|null $jefe_directo_id
 * @property int|null $solicitud_vacante_id
 * @property string $numero_empleado
 * @property string|null $foto_path
 * @property string $nombre_completo
 * @property string|null $apellido_paterno
 * @property string|null $apellido_materno
 * @property string $rfc
 * @property string $curp
 * @property string|null $nss
 * @property string|null $regimen_fiscal
 * @property Carbon $fecha_nacimiento
 * @property string|null $estado_civil
 * @property string|null $domicilio
 * @property string|null $colonia
 * @property string|null $codigo_postal
 * @property string|null $municipio
 * @property string|null $estado_direccion
 * @property string|null $telefono
 * @property string|null $correo_personal
 * @property string|null $contacto_emergencia_nombre
 * @property string|null $contacto_emergencia_telefono
 * @property Carbon $fecha_ingreso
 * @property Carbon|null $fecha_baja
 * @property string|null $motivo_baja
 * @property float $salario_diario
 * @property string $jornada
 * @property string|null $sucursal
 * @property string|null $banco
 * @property string|null $clabe
 * @property string $estatus
 * @property int|null $documentacion_revisado_por_user_id
 * @property Carbon|null $documentacion_fecha_revision
 * @property string|null $documentacion_comentario_revision
 * @property string $documentacion_estatus
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'puesto_id',
    'jefe_directo_id',
    'solicitud_vacante_id',
    'numero_empleado',
    'foto_path',
    'nombre_completo',
    'apellido_paterno',
    'apellido_materno',
    'rfc',
    'curp',
    'nss',
    'regimen_fiscal',
    'fecha_nacimiento',
    'estado_civil',
    'domicilio',
    'colonia',
    'codigo_postal',
    'municipio',
    'estado_direccion',
    'telefono',
    'correo_personal',
    'contacto_emergencia_nombre',
    'contacto_emergencia_telefono',
    'fecha_ingreso',
    'fecha_baja',
    'motivo_baja',
    'salario_diario',
    'jornada',
    'sucursal',
    'banco',
    'clabe',
    'estatus',
    'documentacion_revisado_por_user_id',
    'documentacion_fecha_revision',
    'documentacion_comentario_revision',
    'documentacion_estatus',
])]
#[Hidden(['clabe'])]
class Empleado extends Model
{
    /** @use HasFactory<EmpleadoFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Puesto, $this>
     */
    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }

    /**
     * @return BelongsTo<Empleado, $this>
     */
    public function jefeDirecto(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'jefe_directo_id');
    }

    /**
     * @return BelongsTo<SolicitudVacante, $this>
     */
    public function solicitudVacante(): BelongsTo
    {
        return $this->belongsTo(SolicitudVacante::class);
    }

    /**
     * @return HasMany<Contrato, $this>
     */
    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    /**
     * @return HasMany<Comision, $this>
     */
    public function comisiones(): HasMany
    {
        return $this->hasMany(Comision::class);
    }

    /**
     * @return HasMany<NominaDetalle, $this>
     */
    public function nominaDetalles(): HasMany
    {
        return $this->hasMany(NominaDetalle::class);
    }

    /**
     * @return HasMany<Documento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Años completos de antigüedad a la fecha dada (hoy por defecto).
     */
    public function aniosAntiguedad(?CarbonInterface $fecha = null): int
    {
        return (int) $this->fecha_ingreso->diffInYears($fecha ?? now());
    }

    /**
     * Nombre completo a partir de nombre(s) + apellidos, con fallback a nombre_completo.
     */
    public function nombreCompleto(): string
    {
        $partes = array_filter([$this->nombre_completo, $this->apellido_paterno, $this->apellido_materno]);

        return implode(' ', $partes) ?: $this->nombre_completo;
    }

    /**
     * Iniciales para el avatar (primera letra del nombre y del primer apellido).
     */
    public function iniciales(): string
    {
        $nombre = mb_substr($this->nombre_completo, 0, 1);
        $apellido = mb_substr($this->apellido_paterno ?? '', 0, 1);

        return mb_strtoupper($nombre.$apellido ?: $nombre);
    }

    /**
     * Estado laboral derivado para mostrar en la UI: activo, periodo_prueba o baja.
     * No existe seguimiento de fechas de vacaciones tomadas, por lo que ese estado no se deriva aquí.
     */
    public function estadoLaboral(): string
    {
        if ($this->estatus === 'documentacion_pendiente') {
            return 'documentacion_pendiente';
        }

        if ($this->estatus === 'baja') {
            return 'baja';
        }

        $enPrueba = $this->contratos()
            ->where('tipo', 'periodo_prueba')
            ->where(function ($query) {
                $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now()->toDateString());
            })
            ->exists();

        return $enPrueba ? 'periodo_prueba' : 'activo';
    }

    /**
     * Avance de captura de documentación obligatoria: cuántos tipos ya tienen archivo vs el total requerido.
     *
     * @return array{completados: int, total: int}
     */
    public function progresoDocumentacion(): array
    {
        $tiposObligatorios = DocumentoTipo::query()->where('activo', true)->where('obligatorio', true)->pluck('id');

        $completados = $this->documentos()->whereIn('documento_tipo_id', $tiposObligatorios)->count();

        return ['completados' => $completados, 'total' => $tiposObligatorios->count()];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'fecha_baja' => 'date',
            'salario_diario' => 'decimal:2',
            'documentacion_fecha_revision' => 'datetime',
        ];
    }
}
