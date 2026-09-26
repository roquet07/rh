<?php

namespace App\Models;

use Database\Factories\DocumentoTipoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property bool $obligatorio
 * @property bool $activo
 * @property int $orden
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'nombre',
    'descripcion',
    'obligatorio',
    'activo',
    'orden',
])]
class DocumentoTipo extends Model
{
    /** @use HasFactory<DocumentoTipoFactory> */
    use HasFactory;

    /**
     * @return HasMany<Documento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'obligatorio' => 'boolean',
            'activo' => 'boolean',
        ];
    }
}
