<?php

namespace App\Http\Requests\JobOpening;

use App\Rules\IsHiringManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobOpeningRequest extends FormRequest
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
     * Regras de validação para a criação de uma vaga.
     * created_by não entra aqui — é definido no Controller a partir do usuário autenticado.
     * Cada item de hiring_manager_ids é validado como um usuário com role hiring-manager.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => 'required|uuid|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'type' => 'required|in:full-time,part-time,contract,internship',
            'hiring_manager_ids' => 'sometimes|array',
            'hiring_manager_ids.*' => ['integer', new IsHiringManager()]
        ];
    }
}
