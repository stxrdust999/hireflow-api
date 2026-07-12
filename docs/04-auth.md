# 04 — Autenticação

## Visão geral

O HireFlow possui dois mecanismos de autenticação:

- **Email e senha** — para todos os tipos de usuário, via formulário tradicional
- **OAuth com LinkedIn** — para candidatos, permitindo login/cadastro com um clique

Ambos os mecanismos resultam no mesmo artefato: um **token de API** gerado pelo Sanctum, que o frontend armazena e envia em toda requisição autenticada.

---

## Por que Sanctum?

O Laravel Sanctum é a solução oficial do Laravel para autenticação de SPAs e APIs via tokens. Ele é mais simples que o Laravel Passport (que implementa OAuth 2.0 completo) e adequado para o caso do HireFlow, onde o único cliente da API é o próprio frontend Next.js.

Cada usuário autenticado recebe um token único armazenado na tabela `personal_access_tokens`. Esse token é enviado pelo frontend no header `Authorization: Bearer {token}` em toda requisição que exige autenticação.

---

## Por que LinkedIn para OAuth?

Um ATS é um sistema diretamente relacionado ao mundo profissional. Candidatos já possuem perfil no LinkedIn e é natural que queiram usar essa identidade para se candidatar a vagas — sem criar mais uma conta e senha para lembrar. A integração é feita via **Laravel Socialite**.

Pode ser que, pro final do projeto, outros métodos de autenticação OAuth sejam adicionados.

---

## Fluxo de autenticação — Email e senha

✅ _Implementado — `AuthController` (`register`, `login`, `logout`, `me`), testado ponta a ponta via Postman._

```
1. Usuário envia POST /api/v1/auth/login
   { "email": "...", "password": "..." }

2. API valida as credenciais contra a tabela users

3. Se válidas:
   → Gera um token Sanctum
   → Retorna o token + dados básicos do usuário + suas roles

4. Frontend armazena o token (cookie httpOnly ou localStorage)

5. Toda requisição subsequente envia:
   Authorization: Bearer {token}

6. API valida o token a cada requisição via middleware auth:sanctum
```

### Detalhes da implementação

- **Resposta do login** segue o formato `{ "data": { "token": "...", "user": {...} } }` — montada pelo `LoginResource`, que combina o token Sanctum com o usuário formatado pelo `UserResource`.
- **Usuário inativo é bloqueado no login** mesmo com credenciais corretas — o `AuthController@login` verifica `is_active` após a autenticação.
- **Login com sessão já ativa é bloqueado** — o `LoginRequest::authorize()` retorna `false` se a requisição já carrega um token Sanctum válido (`Auth::guard('sanctum')->check()`). Cada dispositivo tem seu próprio token, então logar em outro dispositivo continua funcionando normalmente.
- **Validação do login não usa `exists` no email** — decisão de segurança para evitar enumeration attack (a resposta de validação não revela se um email existe no sistema).

---

## Fluxo de autenticação — OAuth LinkedIn

🚧 _Implementação pendente — fluxo definido._

```
1. Usuário clica em "Entrar com LinkedIn" no frontend

2. Frontend redireciona para GET /api/v1/auth/linkedin/redirect

3. API redireciona o usuário para a página de autorização do LinkedIn

4. Usuário autoriza o HireFlow no LinkedIn

5. LinkedIn redireciona de volta para GET /api/v1/auth/linkedin/callback
   com um código de autorização

6. API troca o código pelo perfil do usuário via Socialite
   → Busca ou cria o usuário na tabela users
   → Preenche provider = 'linkedin' e provider_id = ID do LinkedIn

7. API gera um token Sanctum para o usuário
   → Redireciona o frontend com o token

8. A partir daqui, o fluxo é idêntico ao de email/senha
```

---

## Registro de novos usuários

Existem dois caminhos de registro, dependendo do tipo de usuário:

### Candidatos

Podem se registrar pelo portal público, via formulário ou OAuth LinkedIn. Ao se registrar, recebem automaticamente a role `candidate`.

✅ _Registro via formulário implementado (`POST /api/v1/auth/register`)._

### Recrutadores, Hiring Managers e Admins

🚧 _Fluxo a definir — provavelmente convite por e-mail gerado por um Admin._

Não é desejável que qualquer pessoa possa se registrar como recrutador. A criação desses usuários será controlada pelo Admin do sistema.

> ⚠️ **Estado temporário conhecido:** o endpoint de registro atual aceita qualquer slug existente na tabela `roles` (validação `exists:roles,slug`), incluindo `recruiter` e `admin`. A restrição para que apenas `candidate` seja permitido no registro público será aplicada quando o fluxo de convite for implementado.

---

## Proteção de rotas na API

Toda rota que exige autenticação utiliza o middleware `auth:sanctum` — ✅ já em uso nas rotas `logout` e `me`. Rotas que exigem uma role específica utilizam um middleware customizado `CheckRole`.

```php
// Rota pública — qualquer um acessa
Route::get('/job-openings', [JobOpeningController::class, 'index']);

// Rota autenticada — qualquer usuário logado
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

// Rota restrita por role
Route::middleware(['auth:sanctum', 'role:recruiter,admin'])->group(function () {
    Route::post('/job-openings', [JobOpeningController::class, 'store']);
});
```

✅ _Middleware `CheckRole` implementado — registrado com o alias `role` no `bootstrap/app.php`. Detalhes em [Roles & Permissões](./05-roles-permissions.md#nível-de-rota--middleware-checkrole)._

---

## Proteção de rotas no Frontend

🚧 _Implementação pendente — Next.js não iniciado._

O `middleware.ts` do Next.js interceptará todas as requisições e verificará:

1. Se o usuário está autenticado (token presente e válido)
2. Se o usuário possui a role necessária para acessar aquela rota

| Rota                    | Acesso                             |
| ----------------------- | ---------------------------------- |
| `/jobs`                 | Público                            |
| `/jobs/[id]/apply`      | Candidato autenticado              |
| `/dashboard/*`          | Admin, Recruiter ou Hiring Manager |
| `/dashboard/settings/*` | Somente Admin                      |

---

## Logout

✅ _Implementado._

O logout invalida o token atual do Sanctum no banco, tornando-o inutilizável imediatamente.

```
DELETE /api/v1/auth/logout
Authorization: Bearer {token}

→ Token deletado da tabela personal_access_tokens
→ Frontend remove o token do armazenamento local
```

> **Detalhe de implementação:** o controller usa `$request->user()` (a instância resolvida pelo middleware `auth:sanctum`), nunca um fetch novo via `User::findOrFail()`. O `currentAccessToken()` — necessário para saber qual token deletar — só existe em memória na instância que passou pela autenticação da requisição atual; uma instância recarregada do banco retorna `null` e quebra o logout.

---

## Segurança

| Prática                | Implementação                                                            |
| ---------------------- | ------------------------------------------------------------------------ |
| Senhas hasheadas       | Cast `hashed` no model `User` — Laravel usa bcrypt automaticamente       |
| Tokens por dispositivo | Sanctum permite múltiplos tokens por usuário (um por dispositivo/sessão) |
| Revogação imediata     | Logout deleta o token do banco — não há janela de validade residual      |
| Anti-enumeration       | Validação do login não revela se um email existe no sistema              |
| Bloqueio de inativos   | `is_active = false` impede login mesmo com credenciais corretas          |
| 401 JSON sem redirect  | `redirectGuestsTo(fn () => null)` no `bootstrap/app.php` — requisição sem token recebe `401 {"message": "Unauthenticated."}` em vez de tentar redirecionar para uma rota `login` web inexistente (o que causava `500 RouteNotFoundException`) |
| HTTPS em produção      | 🚧 A ser configurado no bloco de DevOps                                  |
| CORS                   | 🚧 A ser configurado — somente `hireflow-web` poderá consumir a API      |

Para detalhes sobre o que cada role pode fazer após autenticada, veja [Roles & Permissões](./05-roles-permissions.md).
