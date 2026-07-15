<?php

namespace App\Http\Requests\Application;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MoveApplicationRequest extends FormRequest
{
    /**
     * Determina se o usuário pode realizar essa requisição.
     * Retorna true — o controle por role é feito na rota
     * (role:admin,recruiter,hiring-manager) e a autorização de recurso
     * pela ApplicationPolicy::move no Controller.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para mover uma candidatura de etapa.
     * stage_id é a etapa de destino, informada pelo cliente — o Service
     * não calcula a próxima etapa automaticamente (recruiter/HM têm
     * liberdade de avançar ou retroceder o candidato no pipeline).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'stage_id' => 'required|uuid|exists:job_stages,id'
        ];
    }
}
