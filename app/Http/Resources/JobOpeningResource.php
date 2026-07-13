<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobOpeningResource extends JsonResource
{
    /**
     * Formata uma vaga para a API, incluindo company (inline) e stages.
     * status é exposto como string crua do enum (->value).
     * Espera que company e stages estejam eager-loaded para evitar N+1.
     *
     * @param  Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'type' => $this->type,
            'status' => $this->status->value,
            'company' => [
                'id' => $this->company->id,
                'name' => $this->company->name,
                'logo_url' => $this->company->logo_url,
            ],
            'stages' => $this->stages->map(fn($stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'order' => $stage->order,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
