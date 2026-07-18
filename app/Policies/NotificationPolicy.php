<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    /**
     * Determina se o usuário pode marcar a notificação como lida.
     * Apenas o destinatário da notificação.
     *
     * @param  User         $user
     * @param  Notification $notification
     * @return bool
     */
    public function read(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }
}
