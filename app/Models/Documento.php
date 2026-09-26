<?php

namespace App\Models;

use Database\Factories\DocumentoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $empleado_id
 * @property int $documento_tipo_id
 * @property int|null $subido_por_user_id
 * @property string $path
 * @property string $nombre_original
 * @property string $mime_type
 * @property int $tamano_bytes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'empleado_id',
    'documento_tipo_id',
    'subido_por_user_id',
    'path',
    'nombre_original',
    'mime_type',
    'tamano_bytes',
])]
class Documento extends Model
{
    /** @use HasFactory<DocumentoFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Empleado, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * @return BelongsTo<DocumentoTipo, $this>
     */
    public function documentoTipo(): BelongsTo
    {
        return $this->belongsTo(DocumentoTipo::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por_user_id');
    }
}
