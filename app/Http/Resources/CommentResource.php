<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Formata um comentário para a API. author é inline ({id, name}) — por isso
     * a relação é eager-loaded no Controller. application_id é exposto como
     * coluna (sem navegar a relação, evitando query desnecessária).
     *
     * @param  Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name
            ],
            'body' => $this->body,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
