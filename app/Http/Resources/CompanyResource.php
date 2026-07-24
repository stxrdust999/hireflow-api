<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Formata uma empresa para a API.
     * job_openings só aparece quando a relação foi eager-loaded (whenLoaded) —
     * o index não carrega, o show sim. Evita N+1 e mantém a listagem enxuta.
     * status da vaga é exposto como string crua do enum (->value).
     *
     * @param  Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_url' => $this->logo_url,
            'job_openings' => $this->whenLoaded('jobOpenings', fn() => $this->jobOpenings->map(fn($job) => [
                'id' => $job->id,
                'title' => $job->title,
                'type' => $job->type,
                'status' => $job->status->value,
            ])),
            'created_at' => $this->created_at
        ];
    }
}
