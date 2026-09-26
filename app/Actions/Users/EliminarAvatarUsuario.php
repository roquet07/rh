<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class EliminarAvatarUsuario
{
    public function __invoke(User $user): void
    {
        if ($user->avatar_path === null) {
            return;
        }

        Storage::disk('local')->delete($user->avatar_path);

        $user->update(['avatar_path' => null]);
    }
}
