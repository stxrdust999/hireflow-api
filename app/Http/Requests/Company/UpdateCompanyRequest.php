<?php

namespace App\Http\Requests\Company;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determina se o usuário pode realizar essa requisição.
     * Retorna true — o controle de acesso por role é feito na rota
     * (middleware role:admin), não aqui.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para a edição parcial de uma empresa.
     * slug não entra — é congelado após a criação (não regera no update).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'logo_url' => 'sometimes|nullable|url|max:255'
        ];
    }
}
