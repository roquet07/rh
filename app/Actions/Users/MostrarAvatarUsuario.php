<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MostrarAvatarUsuario
{
    public function __invoke(User $user): StreamedResponse
    {
        abort_unless($user->avatar_path !== null, 404);

        return Storage::disk('local')->response($user->avatar_path, headers: [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
