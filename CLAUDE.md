# Stardust — Contexto de Desenvolvimento & Preferências

> **Uso:** Cole este arquivo em nova sessão com Claude ou Claude Code para manter continuidade. Atualize ao final de cada sessão.

---

## Sobre o usuário (contexto geral)

- **Role:** Frontend intern expandindo para fullstack (Next.js → Laravel/PHP)
- **Stack principal:** Next.js, React, TypeScript, Tailwind, shadcn/ui, Laravel, MySQL, Docker
- **Empresa:** ~10 pessoas em Sorocaba, Brasil
- **Status:** Aprendendo Laravel/PHP **do zero** — HireFlow é projeto de portfólio para consolidar fullstack
- **Formação:** FATEC Sorocaba, CS

---

## Como o usuário prefere ser ensinado

### Regra geral

- **Idioma:** português brasileiro (pt-BR) — sem "tu vais", "vós", ou outras variações de português europeu. Use "você", "vai", "está", etc. Padrão brasileiro mesmo.
- **Sem enrolação.** Breve mas objetivo — não economize na informação, mesmo se ficar extenso
- **Sempre cite fontes.** Não tire informação "do nada". Mostre de onde veio
- **Entenda a intenção.** Não apenas corrija código literal — descubra o que você quer fazer e sugira baseado nisso

### Explicações de código (padrão de resposta)

1. **Explicação didática** — use exemplos do mundo real (caixinhas para ponteiros, etc)
2. **Explicação técnica** — depois, o jargão/termos específicos
3. **Glossário** — abaixo da explicação técnica, defina os termos usados (type cast, heap, etc)

### Geral

- Breve ≠ superficial. Seja conciso mas completo

---

# HireFlow — Documento de Contexto do Projeto

> Este arquivo é o contexto vivo do projeto HireFlow. Deve ser colado no início de toda nova conversa com a IA para garantir continuidade. Atualize-o ao final de cada sessão de desenvolvimento.

---

## O que é o HireFlow

HireFlow é um **ATS (Applicant Tracking System)** fullstack de complexidade empresarial, desenvolvido como projeto de portfólio público no GitHub. O objetivo é cobrir toda a stack utilizada em ambiente profissional, indo além do front-end e desenvolvendo capacidade fullstack real.

O sistema possui dois portais distintos:

- **Portal público** — candidatos se cadastram, buscam vagas e acompanham o status de suas candidaturas
- **Painel interno** — recrutadores, hiring managers e admins gerenciam vagas, candidatos e o pipeline de seleção

---

## Stack & Motivação das escolhas

| Camada           | Tecnologia                                  | Motivação                                                                            |
| ---------------- | ------------------------------------------- | ------------------------------------------------------------------------------------ |
| API              | Laravel (PHP)                               | Consolidada e bem documentada                                                        |
| Banco            | MySQL 8.0 (Docker)                          | Relacional e adequado ao domínio                                                     |
| Cache / Filas    | Redis 7-alpine (Docker)                     | Cache de listagens pesadas + filas de e-mail/notificação                             |
| Servidor local   | Laravel Herd + Nginx                        | Herd simplifica o ambiente PHP no Windows                                            |
| Frontend         | Next.js + TypeScript + Tailwind + shadcn/ui | App Router com separação clara de contextos                                          |
| Client HTTP      | Orval                                       | Gera clients TypeScript tipados a partir do Swagger da API automaticamente           |
| Auth             | Sanctum + Socialite                         | Sanctum para tokens de API; Socialite para OAuth LinkedIn (contexto natural num ATS) |
| Documentação API | L5-Swagger (OpenAPI)                        | Swagger gerado via anotações no próprio código Laravel                               |
| Containerização  | Docker Desktop                              | Isola MySQL e Redis do ambiente host                                                 |

---

## Arquitetura geral

Dois ecossistemas **completamente separados**, sem compartilhamento de código:

```
www/
├── hireflow-api/            ← projeto Laravel
│   ├── docker-compose.yml   ← containers MySQL + Redis
│   └── .env                 ← variáveis do Laravel (inclui credenciais do banco/Redis)
└── hireflow-web/            ← projeto Next.js (ainda não iniciado)
```

**Regra fundamental:** o Next.js nunca acessa o banco diretamente. Toda comunicação é via HTTP/JSON consumindo a API REST do Laravel.

### Por que projetos separados e não um monorepo?

Decisão intencional para simular o ambiente profissional real onde front e back são equipes/repositórios distintos. Também força o front a depender apenas do contrato da API (Swagger), sem atalhos.

---

## Estrutura de pastas

### hireflow-api (Laravel)

```
hireflow-api/
├── app/
│   ├── Enums/               ← enums PHP para status de domínio
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── Auth/
│   │   │   ├── Jobs/
│   │   │   ├── Applications/
│   │   │   └── Admin/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Policies/
│   ├── Services/
│   └── Notifications/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   └── api.php
└── storage/app/resumes/
```

### hireflow-web (Next.js) — ainda não iniciado

```
hireflow-web/
├── app/
│   ├── (public)/            ← portal de candidatos (vagas, inscrição)
│   ├── (auth)/              ← login, register, OAuth callback
│   └── (dashboard)/         ← painel interno (recruiter, admin, HM)
│       ├── jobs/
│       ├── applications/
│       └── settings/
├── components/
├── lib/
│   └── api/                 ← clients gerados pelo Orval
└── middleware.ts             ← proteção de rotas por role
```

---

## Banco de dados

**Conexão:** MySQL 8.0 via Docker, porta 3306
**Database:** `hireflow`
**Credenciais:** geradas com `openssl rand -base64 16`, armazenadas no `.env` do Laravel

### Decisão sobre UUIDs

Todas as tabelas do domínio usam `uuid` como primary key. A exceção é `users`, que mantém o `bigint` auto-increment padrão do Laravel — decisão tomada para manter compatibilidade com o Sanctum e com helpers nativos do Laravel como `foreignId()`.

### Tabelas e seus propósitos

| Tabela                   | Descrição                                                                                                                                                                                             |
| ------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `users`                  | Usuários do sistema. Possui `provider` e `provider_id` para OAuth (adicionados via migration separada). Possui `is_active` booleano (adicionado via migration separada). PK: `bigint` auto-increment. |
| `password_reset_tokens`  | Tokens de reset de senha. Padrão Laravel.                                                                                                                                                             |
| `sessions`               | Sessões ativas. Padrão Laravel.                                                                                                                                                                       |
| `personal_access_tokens` | Tokens do Sanctum. Criada ao publicar o provider do Sanctum.                                                                                                                                          |
| `companies`              | Empresas que publicam vagas.                                                                                                                                                                          |
| `roles`                  | Roles do sistema. Populada via Seeder.                                                                                                                                                                |
| `user_roles`             | Tabela pivot entre `users` e `roles` (relação N:N).                                                                                                                                                   |
| `job_openings`           | Vagas abertas. **Atenção:** o nome original seria `jobs`, mas o Laravel usa essa tabela internamente para o sistema de filas. Renomeada para `job_openings` para evitar conflito.                     |
| `job_stages`             | Etapas do pipeline de cada vaga (ex: Triagem, Entrevista RH).                                                                                                                                         |
| `applications`           | Candidaturas de usuários a vagas.                                                                                                                                                                     |
| `application_stage_logs` | Histórico completo de movimentação de candidatos no pipeline. Serve como auditoria.                                                                                                                   |
| `comments`               | Comentários internos de recrutadores/HMs por candidatura. Candidatos não veem.                                                                                                                        |
| `notifications`          | Notificações in-app (ex: candidato avançou de etapa).                                                                                                                                                 |

---

## Roles & Permissões

### Decisão: implementação manual (sem Spatie Laravel Permission)

O pacote Spatie foi considerado mas descartado. Motivo: o projeto tem fins de aprendizado e o dev quer entender e controlar cada camada do sistema de permissões. O Spatie abstrairia demais essa lógica.

As roles vivem na tabela `roles` e a relação com usuários é via `user_roles` (N:N).

| Role           | Slug             |
| -------------- | ---------------- |
| Admin          | `admin`          |
| Recruiter      | `recruiter`      |
| Hiring Manager | `hiring-manager` |
| Candidate      | `candidate`      |

### Matriz de permissões

| Ação                              | Admin | Recruiter | Hiring Manager | Candidate |
| --------------------------------- | ----- | --------- | -------------- | --------- |
| Gerenciar usuários & roles        | ✓     | —         | —              | —         |
| Criar / editar vagas              | ✓     | ✓         | —              | —         |
| Publicar / fechar vagas           | ✓     | ✓         | —              | —         |
| Ver candidatos de uma vaga        | ✓     | ✓         | só suas vagas  | —         |
| Mover candidato no pipeline       | ✓     | ✓         | só suas vagas  | —         |
| Adicionar comentários             | ✓     | ✓         | ✓              | —         |
| Se candidatar a vagas             | —     | —         | —              | ✓         |
| Ver status da própria candidatura | —     | —         | —              | ✓         |
| Ver dashboard de métricas         | ✓     | ✓         | só suas vagas  | —         |
| Configurações gerais              | ✓     | —         | —              | —         |

---

## Pipeline padrão de uma vaga

Toda vaga criada ganha estas etapas por padrão (customizáveis pelo recrutador):

```
screening → hr-interview → technical-interview → offer → hired
```

(Sigla em inglês no código; em português: Triagem → Entrevista RH → Entrevista Técnica → Proposta → Contratado)

Cada movimentação de etapa:

1. Gera um registro em `application_stage_logs` (auditoria completa)
2. Dispara uma notificação ao candidato

---

## Enums

Enums PHP nativos (backed enums com string) em `app/Enums/`. Usados para tipar status de domínio e evitar magic strings espalhadas pelo código.

### JobOpeningEnum

```php
// app/Enums/JobOpeningEnum.php
// Draft    = 'draft'     ← estado inicial ao criar a vaga
// Published = 'published' ← vaga visível no portal público
// Closed   = 'closed'    ← vaga encerrada, sem novas candidaturas
```

### ApplicationEnum

```php
// app/Enums/ApplicationEnum.php
// Pending    = 'pending'     ← estado inicial ao criar a candidatura
// InProgress = 'in_progress' ← candidato em alguma etapa ativa do pipeline
// Approved   = 'approved'    ← candidato chegou na última etapa do pipeline (contratado)
// Rejected   = 'rejected'    ← candidatura encerrada pelo recrutador
// Withdrawn  = 'withdrawn'   ← candidato desistiu
```

---

## Models — estado atual

Todas as models estão em `app/Models/`. Traits utilizadas:

- **`HasUuids`** — nativa do Laravel. Usa UUID como PK automaticamente. Usada em todas as models exceto `User`.
- **`HasFactory`** — nativa do Laravel. Liga o model à sua Factory para uso em seeders/testes.
- **`Notifiable`** — nativa do Laravel. Necessária no `User` para o sistema de notificações.

### User

```php
// Traits: HasFactory, Notifiable (sem HasUuids — PK é bigint)
// Fillable: name, email, password, provider, provider_id, is_active
// Casts: email_verified_at → datetime, password → hashed, is_active → boolean
// Relacionamentos:
//   roles(): BelongsToMany → Role (via user_roles)
//   managedJobs(): BelongsToMany → JobOpening (via job_opening_hiring_managers) — vagas sob responsabilidade do usuário como Hiring Manager
```

### Role

```php
// Traits: HasFactory, HasUuids
// Fillable: name, slug
// Sem relacionamentos definidos na model (acesso via User)
```

### Company

```php
// Traits: HasFactory, HasUuids
// Fillable: name, slug, logo_url
// Sem relacionamentos definidos ainda
```

### JobOpening

```php
// Traits: HasFactory, HasUuids
// Fillable: company_id, created_by, title, description, location, type, status
// Casts: status → JobOpeningEnum
// Relacionamentos:
//   creator(): BelongsTo → User (FK: created_by)
//   company(): BelongsTo → Company
//   stages(): HasMany → JobStage (FK: job_id)
//   hiringManagers(): BelongsToMany → User (via job_opening_hiring_managers) — HMs responsáveis pela vaga
```

### JobStage

```php
// Traits: HasFactory, HasUuids
// Fillable: job_id, name, order
// Relacionamentos:
//   job(): BelongsTo → JobOpening
```

### Application

```php
// Traits: HasFactory, HasUuids
// Fillable: job_id, current_stage_id, candidate_id, resume_url, status
// Casts: status → ApplicationEnum
// Relacionamentos:
//   candidate(): BelongsTo → User
//   job(): BelongsTo → JobOpening
//   currentStage(): BelongsTo → JobStage (FK: current_stage_id)
//   stageLogs(): HasMany → ApplicationStageLog
//   comments(): HasMany → Comment
```

### ApplicationStageLog

```php
// Traits: HasFactory, HasUuids
// Fillable: application_id, stage_id, moved_by
// Relacionamentos:
//   movedBy(): BelongsTo → User (FK: moved_by)
//   application(): BelongsTo → Application
//   stage(): BelongsTo → JobStage
```

### Comment

```php
// Traits: HasFactory, HasUuids
// Fillable: application_id, author_id, body
// Relacionamentos:
//   application(): BelongsTo → Application
//   author(): BelongsTo → User (FK: author_id)
```

### Notification

```php
// Traits: HasFactory, HasUuids
// Fillable: user_id, type, data, read_at
// Relacionamentos:
//   user(): BelongsTo → User
```

---

## Autenticação

- **Email/senha:** Laravel Sanctum (tokens de API stateless)
- **OAuth:** Laravel Socialite com LinkedIn (contexto natural num ATS — candidatos já têm perfil lá)
- **Guards no frontend:** `middleware.ts` do Next.js protegendo rotas por role

---

## Pacotes instalados (hireflow-api)

| Pacote                   | Versão | Finalidade                 |
| ------------------------ | ------ | -------------------------- |
| `laravel/sanctum`        | ^4.3   | Auth via tokens            |
| `darkaonline/l5-swagger` | ^11.1  | Geração do Swagger/OpenAPI |
| `laravel/socialite`      | ^5.27  | OAuth LinkedIn             |

Configs publicados via `vendor:publish`:

- `config/sanctum.php`
- `config/l5-swagger.php`

---

## Convenções do projeto

- **Commits:** Conventional Commits (`feat:`, `chore:`, `fix:`, `docs:`, `refactor:`)
- **Idioma do código:** inglês (variáveis, métodos, tabelas, commits)
- **Idioma dos comentários:** português (decisão do dev para facilitar leitura)
- **Primary keys:** UUID em todas as tabelas do domínio (exceto `users`)
- **API:** REST, JSON, prefixo `/api/v1/`
- **Domínio local da API:** `hireflow-api.test` (Herd)
- **Tabela de vagas:** `job_openings` (não `jobs` — conflito com fila interna do Laravel)

---

## Arquitetura de Services

Controllers são orquestradores: recebem a request, delegam pro Service, devolvem a resposta HTTP.
Services carregam as regras de negócio: validações de domínio, operações compostas, efeitos colaterais.

**Regra:** se uma operação toca mais de um Model ou dispara efeitos colaterais (log, notificação, fila), ela vai pro Service — nunca direto no Controller.

### Assinatura dos métodos (referência rápida)

```
AuthService
+ register(data: array): User
+ login(data: array): string
+ logout(user: User): void
+ handleOAuthCallback(provider: string, oAuthUser: OAuthUser): string

JobOpeningService
+ create(data: array): JobOpening
+ update(data: array, jobOpening: JobOpening): JobOpening
+ publish(jobOpening: JobOpening): JobOpening
+ close(jobOpening: JobOpening): JobOpening

ApplicationService
+ apply(data: array): Application
+ move(application: Application, stage: JobStage, author: User): Application
+ withdraw(application: Application): void

CommentService
+ create(data: array): Comment
+ delete(comment: Comment): void

NotificationService
+ markAsRead(notification: Notification): Notification
+ markAllAsRead(user: User): void

UserService
+ list(): Collection
+ assignRole(user: User, role: Role): User
+ deactivate(user: User): void
```

### Decisões de implementação dos Services

**AuthService**

- OAuth cria usuário com `Str::random(32)` como password — nunca utilizado, mas a coluna é `NOT NULL`
- OAuth atribui role `candidate` automaticamente — fluxo OAuth é exclusivo do portal público
- Token Sanctum criado dentro do Service (não no Controller) para manter o fluxo encapsulado
- `Auth::user()` retorna `Authenticatable|null` — usar `User::findOrFail(Auth::id())` para tipagem correta

**JobOpeningService**

- Stages padrão são hardcoded no Service (não buscadas do banco) — são conhecidas pelo domínio
- `publish` valida `status === draft` antes de mudar; `close` valida `status === published`
- Status comparado e atualizado sempre via `JobOpeningEnum` — nunca string crua

**ApplicationService**

- `apply` busca a primeira stage da vaga via `orderBy('order')->firstOrFail()` — nunca assume qual é
- `move` detecta automaticamente se é a última stage (`!JobStage::where('order', '>', $stage->order)->exists()`) e atualiza status para `Hired`
- `withdraw` não deleta o registro — apenas marca `status = withdrawn` para preservar auditoria em `application_stage_logs`

**UserService**

- `deactivate` não deleta o usuário — usa `is_active = false` para preservar integridade referencial com vagas, candidaturas e logs existentes. Soft delete foi considerado mas `is_active` é mais explícito para o domínio.
- `assignRole` usa `syncWithoutDetaching` — evita duplicatas na pivot sem remover roles existentes
- `list` filtra `is_active = true` por padrão — usuários desativados não aparecem na listagem

**Atenção futura (Controllers):** o `AuthService@login` não bloqueia usuário inativo — validar `is_active` no Controller ou Middleware ao implementar.

---

## Arquitetura de Controllers, Requests & Resources

Primeira leva implementada nessa sessão — módulo de autenticação email/senha completo (`register`, `login`, `logout`, `me`). Decisões e motivos documentados abaixo pra manter consistência quando os próximos domínios (Jobs, Applications, Comments, Admin) forem implementados.

### Estrutura de pastas

```
app/Http/
├── Controllers/Api/Auth/AuthController.php
├── Requests/Auth/
│   ├── RegisterRequest.php
│   └── LoginRequest.php
└── Resources/
    ├── UserResource.php          ← flat, reaproveitado por qualquer endpoint que devolva User
    └── Auth/LoginResource.php    ← agrupado, específico do fluxo de login
```

**Critério pra agrupar Controllers/Requests por domínio (`Auth/`) em vez de flat:** usado quando o domínio tende a crescer pra múltiplos Controllers. Auth vai ganhar `SocialAuthController` (OAuth) e possivelmente `PasswordResetController` (tabela `password_reset_tokens` já existe no banco) — por isso a pasta compensa desde já. Domínios com Controller único pra sempre podem ficar flat, sem necessidade de subpasta.

**Critério pra criar um Resource novo vs reaproveitar existente:** Resource é sobre a **forma da saída**, não sobre o endpoint. `UserResource` cobre qualquer resposta que seja "um User" (`register`, `me`, e futuramente listagem/edição de usuário no admin) — por isso fica flat em `Resources/`, fora do namespace `Auth/`, já que não é exclusivo desse domínio. `LoginResource` foi criado porque o login devolve uma forma composta (`token` + `user`) que nenhum Resource existente cobria. Regra geral: **não** criar 1 Resource por endpoint mecanicamente — só quando a forma de saída é genuinamente nova.

### Decisões — AuthController

- **`register`** → `201 Created`, reaproveita `UserResource`. Usa `$request->validated()` (nunca `->all()`, evita mass assignment de campos não previstos nas `rules()`).
- **`login`** → `200 OK` (login não cria recurso REST-endereçável, é uma ação). Monta `LoginResource(['token' => ..., 'user' => ...])`. O `User` é buscado via `User::findOrFail(Auth::id())` — tipagem forte, já que `Auth::user()`/`$request->user()` retornam `Authenticatable|null`.
- **`login` bloqueia usuário inativo** (`is_active === false`) lançando `\Exception` — resolve a pendência antiga já documentada mais abaixo em Services.
- **`logout`** → `204 No Content`, sem Resource (sem corpo). **Importante:** usa `$request->user()` direto, **não** `User::findOrFail()`. Motivo: `currentAccessToken()` (usado dentro de `AuthService::logout`) só funciona na instância que o Sanctum resolveu durante a autenticação da requisição atual — refazer o fetch cria uma instância nova sem esse vínculo em memória, quebrando com `Attempt to read property "id" on null`.
- **`me`** → `200 OK`, reaproveita `UserResource`, sem Request dedicada (rota sem input no corpo — token já validado pelo middleware `auth:sanctum` antes do Controller rodar).

### Decisões — RegisterRequest / LoginRequest

- `RegisterRequest::authorize()` → `true` (rota pública, qualquer um pode se cadastrar).
- ✅ **GAP DE SEGURANÇA RESOLVIDO:** `RegisterRequest::rules()` teve `role` alterado de `exists:roles,slug` para `in:candidate` — registro público agora só cria candidatos, fechando a auto-escalação a admin. Registro de recrutador/HM/admin ficará no fluxo de convite por admin (🚧 não implementado). Ver seção "Autorização" abaixo e `docs/04-auth.md`.
- `LoginRequest::authorize()` → `!Auth::guard('sanctum')->check()`. Bloqueia login se já existir um token Sanctum válido **nessa mesma requisição** (mesmo dispositivo já autenticado tentando logar de novo). Guard precisa ser explicitado (`sanctum`) porque o guard padrão da aplicação é `web` (sessão) — API é stateless, `Auth::check()` sem guard nunca reflete autenticação por token.
- `LoginRequest::rules()` propositalmente **sem** `unique`/`exists` no email — evita enumeration attack (não revelar, via erro de validação, se um email existe no sistema).

### OAuth (LinkedIn, e futuramente Google) — decisão de design pra quando implementarmos

Não criar um Controller por provedor (`LinkedInController`, `GoogleController`, etc). `AuthService::handleOAuthCallback(string $provider, ...)` já trata o provedor como **parâmetro**, não como tipo — o fluxo inteiro (redirect, callback, buscar/criar usuário) é idêntico entre provedores, só muda a string. Plano: um único `SocialAuthController` com rotas `auth/{provider}/redirect` e `auth/{provider}/callback`, validando `$provider` contra uma whitelist (`['linkedin', 'google']`).

### Fix de infraestrutura — `bootstrap/app.php`

Requisição não-autenticada numa rota `auth:sanctum` sem header `Accept: application/json` disparava `RouteNotFoundException` (`500`), porque o middleware padrão do Laravel tentava redirecionar pra uma rota `login` web que não existe (API é 100% stateless, sem views Blade). Corrigido forçando `redirectGuestsTo` a nunca redirecionar:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->redirectGuestsTo(fn () => null);
})
```

Agora requisição sem token retorna `401` limpo (`{"message": "Unauthenticated."}`), aproveitando o `shouldRenderJsonWhen` que já força resposta JSON pra tudo em `api/*`.

### Rotas registradas (`routes/api.php`)

```php
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::delete('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });
});
```

`/api` é adicionado automaticamente pelo Laravel (configurado via `install:api` no `bootstrap/app.php`); só precisa declarar `v1` a partir daqui.

---

## Autorização — `hasRole()` & middleware `CheckRole`

Camada de autorização por role, construída antes do primeiro domínio protegido (`JobOpening`). Substitui os 🚧 antigos em `docs/04-auth.md` e `docs/05-roles-permissions.md`.

### `User::hasRole(string|array $roles): bool`

Método no model `User`. Aceita uma slug ou várias (lógica **OR** — true se tiver pelo menos uma). Implementação: `(array) $roles` normaliza o parâmetro, `$this->roles->pluck('slug')` pega as slugs do usuário, e retorna `->intersect($roles)->isNotEmpty()`. Usado tanto pelo `CheckRole` quanto (futuramente) pelas Policies.

**Atenção N+1:** `$this->roles` dispara lazy loading. Ok num request único (`CheckRole`), mas em listagens de vários usuários usar eager loading (`User::with('roles')`).

### Middleware `CheckRole` (`app/Http/Middleware/CheckRole.php`)

- Registrado com o alias `role` no `bootstrap/app.php` via `$middleware->alias(['role' => CheckRole::class])`.
- Uso na rota: `role:admin,recruiter`. O que vem depois dos `:` são parâmetros **variádicos** (`string ...$roles`) — cada vírgula vira um argumento no `handle()`.
- Lógica: pega `$request->user()`, e se `!$user || !$user->hasRole($roles)` → `abort(403, ...)`. Senão `$next($request)`.
- **Sempre encadear após `auth:sanctum`** (`['auth:sanctum', 'role:...']`) — depende do usuário já autenticado. A blindagem `!$user` cobre o caso de uso indevido sem o guard antes.
- `403` (não `401`): `401` = "não sei quem é" (trabalho do `auth:sanctum`); `403` = "sei quem é, mas não pode" (trabalho do `CheckRole`).

### Fix de segurança aplicado junto

`RegisterRequest::rules()` teve `role` alterado de `exists:roles,slug` para `in:candidate` — fecha o gap de auto-cadastro como admin (registro público agora só cria candidatos). Gap documentado anteriormente aqui e em `docs/04-auth.md` está **resolvido**.

---

## Módulo JobOpening — primeiro domínio REST protegido

Primeiro CRUD completo protegido por role. Introduziu vários padrões que valem pros próximos domínios (Application, Comment, Admin).

### Arquivos

```
app/Http/Controllers/Api/Jobs/JobOpeningController.php   ← 7 métodos (index, show, store, update, publish, close, destroy)
app/Http/Requests/JobOpening/StoreJobOpeningRequest.php  ← criação (campos required)
app/Http/Requests/JobOpening/UpdateJobOpeningRequest.php ← edição (campos sometimes)
app/Http/Resources/JobOpeningResource.php                ← saída (flat, reaproveitável)
app/Rules/IsHiringManager.php                            ← Rule custom
```

### Decisões — Controller

- **Route model binding com UUID:** métodos com `{jobOpening}` recebem `JobOpening $jobOpening` já resolvido (404 automático se não existir). Funciona com UUID por causa do `HasUuids`. **O nome do parâmetro na rota (`{jobOpening}`) precisa bater com o do método** — senão o binding não resolve.
- **`created_by` vem do token, não do body:** no `store`, `$data['created_by'] = $request->user()->id` — autoria não pode ser forjada pelo cliente. Por isso `created_by` **não** está nas `rules()`.
- **Eager loading pra evitar N+1:** todo método que devolve vaga(s) carrega `company` e `stages` (`with()` no `index`, `load()` nos demais) porque o Resource sempre acessa essas relações.
- **Padrão de nomenclatura de variável:** quando o método recebe o Model por parâmetro e o Service devolve o mesmo objeto (mutação in-place), **reatribui o parâmetro** (`$jobOpening = $this->service->publish($jobOpening)`) — nunca criar `$publishedJobOpening`/`$closedJobOpening` (nome redundante pra fugir de conflito). `delete` não guarda retorno.
- **`destroy` deleta direto** (`$jobOpening->delete()`), sem Service — operação simples sem efeito colateral. Retorna `204`.
- **`index` filtra `status = published`:** rota pública só expõe vagas publicadas (candidato não vê `draft`/`closed`). Paginação e listagem interna com outros status (via `?status=` role-aware) ficam pro bloco de filtros. Ver `docs/07-api-conventions.md`.
- **Status HTTP:** `store` → 201; `index`/`show`/`update`/`publish`/`close` → 200; `destroy` → 204.

### Decisões — Requests

- **Duas Requests separadas (Store/Update)**, não uma genérica: criar tem campos `required`, editar tem `sometimes` (edição parcial). A diferença de natureza (required vs sometimes) justifica separar. **DRY evitado de propósito:** por só ~6 campos, duplicar é mais legível que abstrair um prefixo dinâmico. Se virar muitos campos/Requests, aí extrai.
- **`authorize()` → `true`:** controle de role fica na rota (`role:admin,recruiter` via `CheckRole`), fonte única. `authorize()` fica reservado pra checagem de recurso específico quando as Policies entrarem.

### Decisão — Rule custom `IsHiringManager`

Valida que cada id em `hiring_manager_ids` é um user **com role hiring-manager** (não só "user existe"). O `exists:users,id` sozinho não cruza a pivot de roles — por isso uma Rule class (`whereKey` + `whereHas('roles', slug=hiring-manager)` + `exists`). Escolha de colocar na Request (não no Service): dá `422` limpo com mensagem clara pro front. Reutilizada nas duas Requests (Store e Update) — esse é o reuso que **vale** (uma Rule pra duas Requests), diferente de abstrair as regras inteiras.

### Decisão — Resource enxuto por enquanto

`JobOpeningResource` expõe só campos públicos + `company` (inline) + `stages`. **`creator` e `hiringManagers` ficaram de fora de propósito** — são dados internos. A ideia de visão diferenciada (público vs gerência, via `mergeWhen` por role) foi discutida mas adiada (YAGNI) — será revisitada quando o front definir o que cada tela precisa. `company` inline agora; vira `CompanyResource` quando o módulo Company existir. `status` exposto como `->value` (string crua do enum).

### Fix — status inicial no Service

`JobOpeningService::create` não passava `status`, contando com o `default('draft')` do banco. Mas o **default do banco não reflete na instância em memória** retornada por `create()` — `$jobOpening->status` vinha `null`, quebrando o Resource (`->value` on null) só no `store` (index/show leem do banco, ok). Corrigido setando `'status' => JobOpeningEnum::Draft` explícito no `create` — regra de domínio explícita, não default silencioso.

### Convenção REST reafirmada

CRUD usa verbo HTTP (`GET`/`POST`/`PUT`/`DELETE` sobre `/job-openings[/{id}]`); ações não-CRUD ganham sufixo (`/publish`, `/close` via `PATCH`). Híbrido canônico. RPC (ação na URL, tipo `/list`) foi considerado mas descartado — briga com Orval/Swagger e foge do contrato. `apiResource()` não usado aqui porque as rotas se dividem em 3 grupos de middleware (público / admin+recruiter / só admin).

---

## Módulo Application — primeiro domínio com autorização de recurso (Policies)

Módulo mais denso até aqui. Introduziu Policies, rotas aninhadas e a distinção entre autorizar e escopar.

### Arquivos

```
app/Http/Controllers/Api/Applications/ApplicationController.php  ← 6 métodos
app/Http/Requests/Application/StoreApplicationRequest.php        ← só resume_url
app/Http/Requests/Application/MoveApplicationRequest.php         ← só stage_id
app/Http/Resources/ApplicationResource.php
app/Policies/ApplicationPolicy.php                               ← view, move, withdraw
app/Policies/JobOpeningPolicy.php                                ← viewApplications
```

### Decisão — Policies agora (não depois)

Este é o primeiro domínio onde `role:` na rota **não basta**: "candidato saca candidatura" é role, mas "candidato saca a **própria**" é recurso. Alternativa considerada e descartada: `if` de posse dentro dos métodos, formalizando em Policy depois — descartada porque espalhar `if` de autorização é exatamente o anti-padrão que Policy resolve, e a dívida seria refatorada logo em seguida.

### Policies — como funcionam aqui

- Invocadas com `$this->authorize('ability', $recurso)` no topo do método. **Não** retornam bool pra ramificar — **lançam** `403` e o método para. Nunca usar dentro de `if`.
- Auto-discovery por convenção (`Application` → `ApplicationPolicy`), sem registro manual.
- **Pré-requisito Laravel 11+:** o `Controller` base vem **vazio** — o trait `AuthorizesRequests` não é mais incluído por padrão. Foi adicionado em `app/Http/Controllers/Controller.php`; sem ele, `$this->authorize()` não existe.
- `ApplicationPolicy::move` delega pra `view` (regra idêntica) — reuso que vale, não abstração forçada.
- `JobOpeningPolicy::viewApplications` autoriza contra a **vaga**, não contra a candidatura: a ação é listagem, não existe uma Application específica pra autorizar. Por isso vive na Policy do JobOpening.

### Decisão — Policy vs escopo de query

Nem toda restrição é Policy:

- **Policy** = "pode agir sobre **este recurso**?" — precisa de um recurso.
- **Escopo (`where`)** = listagem das próprias coisas. `myApplications` filtra `where('candidate_id', Auth::id())` — sem Policy, porque é **fisicamente impossível** retornar candidatura alheia. Filtrar ≠ autorizar.
- Mas `index` (candidaturas de uma vaga) **precisa** de Policy mesmo sendo listagem — o filtro é por vaga, não por usuário, então a autorização recai sobre a vaga.

Regra: listar as minhas coisas = filtrar por mim. Tocar em coisa específica (ou listar coisas de recurso de terceiro) = Policy.

### Decisões — Controller

- **Rotas aninhadas + binding duplo:** `store(StoreApplicationRequest $request, JobOpening $jobOpening)` — Request resolvida por tipo, JobOpening por nome+tipo da rota (`{jobOpening}`).
- **Três origens de dado no `store`:** `resume_url` (corpo), `candidate_id` (token, via `Auth::id()`), `job_id` (URL, via `$jobOpening->id`). Os dois últimos nunca vêm do corpo.
- **Por que `job_id` na URL e não no corpo:** (1) hierarquia REST — candidatura pertence à vaga; (2) fonte única — evita URL dizer vaga A e corpo dizer vaga B; (3) binding já valida existência com `404` de graça.
- **Nomes divergem de propósito:** Controller usa vocabulário REST (`store`, `index`, `show`), Service usa vocabulário de domínio (`apply`, `move`, `withdraw`). `store` ↔ `apply` é a mesma ação em duas linguagens.

### Decisão — movimentação livre no pipeline

`stage_id` (destino) vem do **corpo**; o Service **não** calcula a próxima etapa. Recruiter/HM podem avançar, pular ou **retroceder** o candidato — decisão consciente: o domínio real de recrutamento precisa reavaliar candidatos. Mover pra última etapa marca `approved` (lógica já existente no Service).

### Decisão — duplicata no Service (não na Request)

Checagem "mesmo `candidate_id` + `job_id` já existe" ficou no `ApplicationService::apply`, não numa rule `unique` composta. Motivo: é regra de domínio, e `candidate_id`/`job_id` nem estão no corpo (vêm de token/URL) — forçar `unique` composto na Request seria desajeitado. Lança `\Exception`.

### Bug de rota encontrado no teste (lição)

`GET /me/applications` foi movida pra raiz do `v1` e **perdeu os middlewares** (`auth:sanctum`, `role:candidate`) — eles moravam no grupo de onde ela saiu. Sintoma: lista sempre vazia (sem `auth:sanctum`, `Auth::id()` é `null`, e `where('candidate_id', null)` não casa com nada) **e** endpoint público. Lição: **mover rota entre grupos carrega a URL, não os middlewares.**

---

## Módulo Comment — comentários internos por candidatura

Módulo enxuto. Reforçou padrões (reuso de Policy, decisão de `with()`) e trouxe uma terceira forma de regra de Policy.

### Arquivos

```
app/Http/Controllers/Api/Comments/CommentController.php  ← index, store, destroy
app/Http/Requests/Comment/StoreCommentRequest.php        ← só 'comment'
app/Http/Resources/CommentResource.php
app/Policies/CommentPolicy.php                            ← delete
```

### Decisão — reuso da `ApplicationPolicy::view` (não criar ability nova)

`index` e `store` de comentário precisam autorizar contra a **candidatura** ("esse HM pode mexer nesta candidatura?"). Essa pergunta já é respondida pela `ApplicationPolicy::view` — então os dois métodos chamam `$this->authorize('view', $application)`, sem criar ability nova. Reuso que vale: uma ability servindo dois módulos.

### Decisão — `CommentPolicy::delete` = "admin OU autor"

Terceira forma de regra de Policy no projeto (além de "interno ou dono-da-vaga" e "só o dono"): admin passa sempre, senão precisa ser o autor (`$user->id === $comment->author_id`). Role na rota libera as 3 roles internas; a Policy estreita.

### Decisão — `with()` derivado do Resource (regra fechada)

Fechou a regra de eager loading: **`with()` = exatamente as relações que o Resource navega**. Distinção-chave: `$this->author_id` é **coluna** (grátis, já veio no registro); `$this->author->name` é **relação navegada** (exige `with('author')`). Se o Resource só precisa da FK, não navega e o `with()` fica vazio. `CommentResource` expõe `author: {id, name}` (nome, não só id) — por isso navega `author` e o `index`/`store` carregam `with('author')`/`load('author')`. `application_id` fica como coluna crua.

### Pegadinha — chave `comment` vs coluna `body`

O `CommentService::create` recebe `$data['comment']` mas grava na coluna `body`. Então a `StoreCommentRequest` valida `comment` (não `body`) — `body` é só o nome interno da coluna.

### Correção de pastas

Criadas inicialmente fora do padrão (`Requests/Comments/` plural, `Controllers/Api/Comment/` singular) e movidas pro padrão do projeto: **Requests no singular** (`Requests/Comment/`), **Controllers no plural** (`Controllers/Api/Comments/`).

---

## Ordem de desenvolvimento

```
Infra (✓) → API (em andamento) → Front → Docs → DevOps/CI-CD
```

### Bloco API — progresso detalhado

1. ~~Configurar Herd~~ ✓
2. ~~Instalar dependências~~ ✓
3. ~~Migrations~~ ✓
4. ~~Models + Relationships~~ ✓
5. ~~Factories & Seeders~~ ✓
6. ~~Services~~ ✓
7. Controllers + Routes + Requests — 🔄 em andamento (Auth ✓, JobOpening ✓, Application ✓, Comment ✓; Admin pendente)
8. Policies — 🔄 em andamento (`ApplicationPolicy` ✓, `JobOpeningPolicy` ✓; demais domínios conforme necessidade)
9. Swagger

---

## Estado atual

- Infra: **concluída**
- Docker: MySQL 8.0 + Redis 7-alpine rodando
- Herd: servindo `hireflow-api.test`
- Migrations: todas rodadas — inclui migrations adicionais para `provider`/`provider_id` e `is_active` em `users`
- Models: todas atualizadas com fillable, casts e enums corretos
- Enums: `JobOpeningEnum`, `ApplicationEnum` criados em `app/Enums/`
- Factories: **8 factories criadas**
- Seeders: **9 seeders criados** — orquestrados pelo `DatabaseSeeder`
    - `RoleSeeder`: 4 roles (admin, recruiter, hiring-manager, candidate)
    - `UserSeeder`: admin via `.env` + 5 recruiters + 5 HMs + 20 candidates (31 total)
    - `CompanySeeder`: 15 empresas aleatórias via `CompanyFactory`
    - `JobOpeningSeeder`: 20 vagas vinculadas a empresas e recrutadores existentes
    - `JobStageSeeder`: 5 etapas em inglês (`screening` → `hired`) por vaga
    - `ApplicationSeeder`: 40 candidaturas com candidate, vaga e etapa selecionados aleatoriamente
    - `ApplicationStageLogSeeder`: 60 registros de movimentação (`moved_by` = recruiter ou HM)
    - `CommentSeeder`: 50 comentários (autores: recruiters, HMs e candidates)
    - `NotificationSeeder`: 80 notificações para todos os usuários
- Admin credentials: configuráveis via `config/services.php` ← `.env` (`ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`)
- `/docs/`: 12 arquivos de documentação
- Services: **6 services concluídos** com PHPDoc completo
- `php artisan install:api` executado — `routes/api.php` criado, Sanctum religado no `bootstrap/app.php`
- **Módulo Auth completo:** `AuthController` (`register`, `login`, `logout`, `me`), `RegisterRequest`, `LoginRequest`, `UserResource`, `LoginResource` — todos com PHPDoc, testados via Postman ponta a ponta
- Fix aplicado em `bootstrap/app.php`: `redirectGuestsTo(fn () => null)` — evita `500` em rota protegida sem token (ver detalhes em [Arquitetura de Controllers, Requests & Resources](#arquitetura-de-controllers-requests--resources))
- Constraint do `laravel/sanctum` no `composer.json` foi alterado de `^4.3` pra `^4.0` pelo próprio `install:api` (efeito colateral do comando, não escolha manual) — revertido pra `^4.3` manualmente
- **Camada de autorização completa:** `User::hasRole()`, middleware `CheckRole` (alias `role` no `bootstrap/app.php`), fix de segurança no `RegisterRequest` (`in:candidate`) — todos com PHPDoc, testados via Postman (401/403/200). Ver seção "Autorização — `hasRole()` & middleware `CheckRole`"
- **Módulo JobOpening completo:** `JobOpeningController` (7 métodos CRUD + publish/close), `StoreJobOpeningRequest`, `UpdateJobOpeningRequest`, `JobOpeningResource`, Rule custom `IsHiringManager` — todos com PHPDoc, rotas protegidas por `role:` (público / admin+recruiter / só admin no delete), testados via Postman ponta a ponta (201/200/204/401/403/422). Ver seção "Módulo JobOpening — primeiro domínio REST protegido"
- Fix aplicado no `JobOpeningService::create`: `status` setado explícito como `JobOpeningEnum::Draft` (default do banco não reflete na instância em memória)
- Repositório remoto: atualizado
- **Módulo Application completo:** `ApplicationController` (6 métodos), `StoreApplicationRequest`, `MoveApplicationRequest`, `ApplicationResource`, `ApplicationPolicy` (view/move/withdraw), `JobOpeningPolicy` (viewApplications) — todos com PHPDoc, testados via Postman incluindo os cenários de Policy (HM não-dono → 403, candidato sacando candidatura alheia → 403). Ver seção "Módulo Application — primeiro domínio com autorização de recurso (Policies)"
- Trait `AuthorizesRequests` adicionado ao `Controller` base (Laravel 11+ não inclui por padrão) — sem ele `$this->authorize()` não existe
- Fix aplicado no `ApplicationService::apply`: bloqueio de candidatura duplicada (mesmo `candidate_id` + `job_id`)
- Fix aplicado em `routes/api.php`: `me/applications` estava sem `auth:sanctum`/`role:candidate` após ser movida de grupo — retornava lista vazia e ficava pública
- **Módulo Comment completo:** `CommentController` (index/store/destroy), `StoreCommentRequest`, `CommentResource`, `CommentPolicy` (delete = admin ou autor) — todos com PHPDoc, testados via Postman incluindo cenários de Policy (não-autor não-admin → 403, admin → 204, autor → 204). `index`/`store` reutilizam `ApplicationPolicy::view`. Ver seção "Módulo Comment — comentários internos por candidatura"
- **Próximo passo: módulo Admin (gestão de usuários e empresas) — `UserController`/`CompanyController`, provavelmente com `UserService` (já existe: list/assignRole/deactivate). Depois: Notifications. Pendências: paginação nas listagens (todos os módulos), fluxo de convite de usuário interno, `CompanyResource`, Swagger**
