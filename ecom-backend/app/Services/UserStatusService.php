<?php

namespace App\Services;

use App\Events\UserStatusChanged;
use Illuminate\Support\Facades\Broadcast;

class UserStatusService
{
    public function updateStatus($userId, $status)
    {
        // Mettre à jour l'état dans la base de données
        \App\Models\User::where('id', $userId)->update(['online' => $status]);

        // Diffuser l'événement
        broadcast(new UserStatusChanged($userId, $status))->toOthers();
    }

    public function isOnline($userId)
    {
        return \App\Models\User::where('id', $userId)->value('online') === 1;
    }
}
