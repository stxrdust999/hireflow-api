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

### Recrutadores, Hiring Managers e Admins

🚧 _Fluxo a definir — provavelmente convite por e-mail gerado por um Admin._

Não é desejável que qualquer pessoa possa se registrar como recrutador. A criação desses usuários será controlada pelo Admin do sistema.

---

## Proteção de rotas na API

Toda rota que exige autenticação utiliza o middleware `auth:sanctum`. Rotas que exigem uma role específica utilizam um middleware customizado `CheckRole`.

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

🚧 _Middleware `CheckRole` ainda não implementado._

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

O logout invalida o token atual do Sanctum no banco, tornando-o inutilizável imediatamente.

```
DELETE /api/v1/auth/logout
Authorization: Bearer {token}

→ Token deletado da tabela personal_access_tokens
→ Frontend remove o token do armazenamento local
```

---

## Segurança

| Prática                | Implementação                                                            |
| ---------------------- | ------------------------------------------------------------------------ |
| Senhas hasheadas       | Cast `hashed` no model `User` — Laravel usa bcrypt automaticamente       |
| Tokens por dispositivo | Sanctum permite múltiplos tokens por usuário (um por dispositivo/sessão) |
| Revogação imediata     | Logout deleta o token do banco — não há janela de validade residual      |
| HTTPS em produção      | 🚧 A ser configurado no bloco de DevOps                                  |
| CORS                   | 🚧 A ser configurado — somente `hireflow-web` poderá consumir a API      |

Para detalhes sobre o que cada role pode fazer após autenticada, veja [Roles & Permissões](./05-roles-permissions.md).
