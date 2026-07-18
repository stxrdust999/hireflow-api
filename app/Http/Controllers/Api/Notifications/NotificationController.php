<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $service
    ) {
    }

    /**
     * Lista as notificações do usuário autenticado.
     * Não usa Policy: a segurança vem do escopo da query (where user_id),
     * tornando impossível retornar notificação de outro usuário.
     * Rota protegida (qualquer usuário autenticado, sem restrição de role).
     *
     * @return JsonResponse 200 com a coleção de notificações
     */
    public function index(): JsonResponse
    {
        $notifications = Notification::where('user_id', Auth::id())->get();

        return NotificationResource::collection($notifications)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Marca uma notificação específica como lida.
     * A NotificationPolicy::read garante que apenas o destinatário possa
     * marcá-la — diferente do index, aqui há um recurso específico (id na URL).
     * Rota protegida (qualquer usuário autenticado).
     *
     * @param  Notification $notification Notificação resolvida pelo UUID na rota
     * @return JsonResponse 200 com a notificação atualizada
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException 403 se a notificação não for do usuário
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $this->authorize('read', $notification);

        $notification = $this->service->markAsRead($notification);

        return (new NotificationResource($notification))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Marca todas as notificações não lidas do usuário autenticado como lidas.
     * Não usa Policy: o Service já filtra pelo usuário recebido, então não há
     * como afetar notificações de terceiros.
     * Rota protegida (qualquer usuário autenticado).
     *
     * @return JsonResponse 204 sem corpo
     */
    public function markAllAsRead(): JsonResponse
    {
        $this->service->markAllAsRead(User::findOrFail(Auth::id()));

        return response()->json(null, 204);
    }


}
