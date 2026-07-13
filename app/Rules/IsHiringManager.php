<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class IsHiringManager implements ValidationRule
{
    /**
     * Valida que o valor é o id de um usuário que possui a role 'hiring-manager'.
     * Falha (com mensagem) se o usuário não existir ou não for hiring-manager.
     *
     * @param  string  $attribute Nome do campo em validação
     * @param  mixed   $value     Id do usuário a verificar
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isHiringManager = User::whereKey($value)
            ->whereHas('roles', fn($q) => $q->where('slug', 'hiring-manager'))
            ->exists();

        if (!$isHiringManager)
            $fail("O usuário {$value} não é um Gerente de Contratações.");
    }
}
