<?php

namespace App\Models;

use Database\Factories\EmpresaConfigFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string|null $escritura_constitutiva
 * @property string|null $poder_representante
 * @property string|null $ciudad_firma
 * @property string|null $entidad_jurisdiccion
 * @property string|null $correo_privacidad
 * @property string|null $url_aviso_privacidad
 * @property string|null $horario_privacidad
 * @property int $id
 * @property string $razon_social
 * @property string|null $nombre_comercial
 * @property string $rfc
 * @property string|null $tamano
 * @property string $domicilio_fiscal
 * @property string|null $registro_patronal_imss
 * @property string|null $representante_legal
 * @property string|null $logo_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'escritura_constitutiva',
    'poder_representante',
    'ciudad_firma',
    'entidad_jurisdiccion',
    'correo_privacidad',
    'url_aviso_privacidad',
    'horario_privacidad',

    'razon_social',
    'nombre_comercial',
    'rfc',
    'tamano',
    'domicilio_fiscal',
    'registro_patronal_imss',
    'representante_legal',
    'logo_path',
])]
class EmpresaConfig extends Model
{
    /** @use HasFactory<EmpresaConfigFactory> */
    use HasFactory;

    protected $table = 'empresa_config';

    /**
     * Get the single empresa configuration row, creating an empty one if it doesn't exist yet.
     */
    public static function current(): self
    {
        return self::query()->firstOrNew(['id' => 1]);
    }
}
