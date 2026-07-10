<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Formata os dados públicos de um usuário para a API.
     * Nunca expõe password, provider ou provider_id.
     *
     * @param  Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $returned_fields = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->roles->pluck('slug'),
            'is_active' => $this->is_active
        ];

        return $returned_fields;
    }
}
