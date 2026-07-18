<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Formata uma notificação para a API.
     * Expõe is_read (booleano derivado de read_at) além do próprio read_at —
     * o front normalmente só precisa saber se foi lida ou não.
     * user_id não é exposto: o endpoint já é escopado no usuário autenticado.
     *
     * @param  Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => $this->data,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at
        ];
    }
}
