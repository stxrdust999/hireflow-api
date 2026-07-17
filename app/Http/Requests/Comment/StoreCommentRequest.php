<?php

namespace App\Http\Requests\Comment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determina se o usuário pode realizar essa requisição.
     * Retorna true — o controle por role é feito na rota e a autorização
     * de recurso pela ApplicationPolicy::view no Controller.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para a criação de um comentário.
     * Apenas o texto vem do corpo, sob a chave 'comment' (o Service grava na
     * coluna 'body'). application_id (URL) e author_id (token) são definidos
     * no Controller, por isso fora das rules.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comment' => 'required|string'
        ];
    }
}
