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
7. Controllers + Routes + Requests
8. Policies
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
- Repositório remoto: atualizado
- **Próximo passo: Controllers + Routes + Requests**
