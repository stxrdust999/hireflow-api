<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Marca uma notificação específica como lida.
     * Define o timestamp 'read_at' com o momento atual.
     *
     * @param  Notification $notification Notificação resolvida via route model binding
     * @return Notification
     */
    public function markAsRead(Notification $notification): Notification
    {
        $notification->update([
            'read_at' => now()
        ]);

        return $notification;
    }

    /**
     * Marca todas as notificações não lidas de um usuário como lidas.
     * Filtra apenas notificações com 'read_at' nulo para evitar
     * updates desnecessários em notificações já lidas.
     *
     * @param  User $user Usuário autenticado (via $request->user() no Controller)
     * @return void
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)->whereNull('read_at')->update([
            'read_at' => now()
        ]);
    }
}
