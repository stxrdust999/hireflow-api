<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LoginRequest extends FormRequest
{
    /**
     * Determina se o usuário pode realizar essa requisição.
     * Bloqueia o login se já existir um token Sanctum válido na requisição atual —
     * evita reautenticar um dispositivo que já está logado.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return !Auth::guard('sanctum')->check();
    }

    /**
     * Regras de validação para o login.
     * Propositalmente sem 'unique'/'exists' no email — evita enumeration attack,
     * não revelando pela resposta de validação se um email existe no sistema.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
