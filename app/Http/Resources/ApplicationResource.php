<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Formata uma candidatura para a API, incluindo candidate, job e
     * current_stage (todos inline). status é exposto como string crua do enum.
     * Espera que as três relações estejam eager-loaded para evitar N+1.
     *
     * stageLogs e comments ficam de fora de propósito — são dados pesados,
     * expostos em endpoints próprios.
     *
     * @param  Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'resume_url' => $this->resume_url,
            'candidate' => [
                'id' => $this->candidate->id,
                'name' => $this->candidate->name
            ],
            'job' => [
                'id' => $this->job->id,
                'title' => $this->job->title
            ],
            'current_stage' => [
                'id' => $this->currentStage->id,
                'name' => $this->currentStage->name,
                'order' => $this->currentStage->order,
            ],
            'created_at' => $this->created_at
        ];
    }
}
