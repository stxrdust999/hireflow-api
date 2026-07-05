<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as OAuthUser;

class AuthService
{
    /**
     * Registra um novo usuário e atribui uma role a ele.
     *
     * @param  array{name: string, email: string, password: string, role: string} $data
     * @return User
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Role não encontrada
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        // vamos enviar o campo como 'role' pelo front por ser mais semantico.
        $role = Role::where('slug', $data['role'])->firstOrFail();

        $user->roles()->attach($role->id);

        return $user;
    }

    /**
     * Autentica um usuário via email e senha e retorna um token Sanctum.
     *
     * @param  array{email: string, password: string} $data
     * @return string Token de acesso plaintext
     *
     * @throws \Exception Credenciais inválidas
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Usuário não encontrado após autenticação
     */
    public function login(array $data): string
    {
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            throw new \Exception('Credenciais inválidas');
        }

        $user = User::findOrFail(Auth::id());

        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * Revoga o token Sanctum utilizado na requisição atual.
     *
     * @param  User $user Usuário autenticado (via $request->user() no Controller)
     * @return void
     */
    public function logout(User $user): void
    {
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
    }

    /**
     * Autentica ou registra um usuário via OAuth (ex: LinkedIn) e retorna um token Sanctum.
     * Usuários criados via OAuth recebem a role 'candidate' automaticamente
     * e uma senha aleatória (nunca utilizada).
     *
     * @param  string    $provider   Provedor OAuth (ex: 'linkedin')
     * @param  OAuthUser $oAuthUser  Objeto do usuário retornado pelo Socialite
     * @return string Token de acesso plaintext
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Role 'candidate' não encontrada
     */
    public function handleOAuthCallback(string $provider, OAuthUser $oAuthUser): string
    {
        $user = User::where('provider', $provider)
            ->where('provider_id', $oAuthUser->getId())
            ->first();

        if (!$user) {
            $user = User::create([
                'name'        => $oAuthUser->getName(),
                'email'       => $oAuthUser->getEmail(),
                'password'    => Str::random(32),
                'provider'    => $provider,
                'provider_id' => $oAuthUser->getId(),
            ]);

            $role = Role::where('slug', 'candidate')->firstOrFail();

            $user->roles()->attach($role->id);
        }

        return $user->createToken('auth_token')->plainTextToken;
    }
}
