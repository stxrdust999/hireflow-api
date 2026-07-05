<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

class UserService
{
    /**
     * Retorna todos os usuários ativos do sistema.
     * Utilizado pelo admin para listagem e gerenciamento de usuários.
     *
     * @return Collection<int, User>
     */
    public function list(): Collection
    {
        $users = User::all()->where('is_active', true);

        return $users;
    }

    /**
     * Atribui uma role a um usuário.
     * Utiliza syncWithoutDetaching para evitar duplicatas na tabela pivot
     * sem remover roles já existentes.
     *
     * @param  User $user Usuário resolvido via route model binding
     * @param  Role $role Role resolvida via route model binding
     * @return User
     */
    public function assignRole(User $user, Role $role): User
    {
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    /**
     * Desativa um usuário do sistema sem deletar seu registro.
     * Preserva integridade referencial com vagas, candidaturas
     * e logs de auditoria existentes.
     *
     * @param  User $user Usuário resolvido via route model binding
     * @return void
     */
    public function deactivate(User $user): void
    {
        $user->update([
            'is_active' => false
        ]);
    }
}
