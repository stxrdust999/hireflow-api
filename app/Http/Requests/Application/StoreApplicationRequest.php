<?php

namespace App\Http\Requests\Application;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    /**
     * Determina se o usuário pode realizar essa requisição.
     * Retorna true — o controle de acesso por role é feito na rota
     * (middleware role:candidate), não aqui.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para a criação de uma candidatura.
     * Apenas resume_url vem do corpo: candidate_id é obtido do usuário
     * autenticado e job_id vem da URL (rota aninhada) — ambos definidos
     * no Controller, por isso fora das rules.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resume_url' => 'required|url'
        ];
    }
}
