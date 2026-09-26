<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Support\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ActualizarAvatarUsuario
{
    private const MAX_DIMENSION = 800;

    private const JPEG_QUALITY = 80;

    /**
     * Comprime y guarda el avatar del usuario, reemplazando el anterior si existía.
     */
    public function __invoke(User $user, UploadedFile $archivo): User
    {
        if ($user->avatar_path !== null) {
            Storage::disk('local')->delete($user->avatar_path);
        }

        [$contenido] = ImageCompressor::comprimir($archivo, self::MAX_DIMENSION, self::JPEG_QUALITY);
        $path = 'usuarios/avatares/'.$user->id.'.jpg';

        Storage::disk('local')->put($path, $contenido);

        $user->update(['avatar_path' => $path]);

        return $user;
    }
}
