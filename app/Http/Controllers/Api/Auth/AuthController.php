<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service)
    {
    }

    /**
     * Registra um novo usuário e retorna seus dados formatados.
     *
     * @param  RegisterRequest $request
     * @return JsonResponse 201 com o usuário criado
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->service->register($request->validated());

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    /**
     * Autentica um usuário via email e senha.
     * Bloqueia usuários inativos mesmo com credenciais corretas.
     *
     * @param  LoginRequest $request
     * @return JsonResponse 200 com o token Sanctum e os dados do usuário
     *
     * @throws \Exception Usuário inativo
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->service->login($request->validated());
        $user = User::findOrFail(Auth::id());

        if (!$user->is_active) {
            throw new \Exception('Usuário inativo. Não é possível fazer login.');
        }

        return (new LoginResource([
            'token' => $token,
            'user' => $user
        ]))->response()->setStatusCode(200);
    }

    /**
     * Revoga o token Sanctum utilizado na requisição atual.
     *
     * @param  Request $request
     * @return JsonResponse 204 sem corpo
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->service->logout($user);

        return response()->json(null, 204);
    }

    /**
     * Retorna os dados do usuário autenticado na requisição atual.
     *
     * @param  Request $request
     * @return JsonResponse 200 com o usuário formatado
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return (new UserResource($user))->response()->setStatusCode(200);
    }
}
