<?php

namespace App\Http\Requests\JobOpening;

use App\Rules\IsHiringManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobOpeningRequest extends FormRequest
{
    /**
     * Determina se o usuário pode realizar essa requisição.
     * Retorna true — o controle de acesso por role é feito na rota
     * (middleware role:admin,recruiter), não aqui.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para a edição de uma vaga.
     * Todos os campos usam 'sometimes' — só são validados se presentes,
     * permitindo edição parcial (envia só o que quer alterar).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => 'sometimes|uuid|exists:companies,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'nullable|string|max:255',
            'type' => 'sometimes|in:full-time,part-time,contract,internship',
            'hiring_manager_ids' => 'sometimes|array',
            'hiring_manager_ids.*' => ['integer', new IsHiringManager()],
        ];
    }
}
