# HireFlow — Contexto do Projeto para IA

> Este arquivo é o contexto vivo do projeto. Deve ser colado no início de toda nova conversa com a IA para garantir continuidade. Atualize-o ao final de cada sessão de desenvolvimento.

---

## Visão geral

**HireFlow** é um sistema ATS (Applicant Tracking System) fullstack com dois ecossistemas completamente separados:

- `www/hireflow-api` — API REST em Laravel
- `www/hireflow-web` — Frontend em Next.js (ainda não iniciado)

Os dois projetos não compartilham código. A comunicação é 100% via HTTP/JSON. O Next.js nunca acessa o banco diretamente.

---

## Stack

| Camada              | Tecnologia                                                    |
| ------------------- | ------------------------------------------------------------- |
| API                 | Laravel (PHP)                                                 |
| Banco de dados      | MySQL 8.0 (Docker)                                            |
| Cache / Filas       | Redis 7-alpine (Docker)                                       |
| Servidor local      | Laravel Herd + Nginx                                          |
| Frontend            | Next.js + TypeScript + Tailwind CSS + shadcn/ui               |
| Client HTTP gerado  | Orval (a partir do Swagger)                                   |
| Autenticação        | Laravel Sanctum (tokens) + Laravel Socialite (OAuth LinkedIn) |
| Documentação da API | L5-Swagger (OpenAPI)                                          |
| Containerização     | Docker Desktop                                                |

---

## Estrutura de pastas

```
www/
├── docker-compose.yml       ← containers MySQL + Redis
├── .env                     ← variáveis do Docker (separado do .env do Laravel)
├── hireflow-api/            ← projeto Laravel
└── hireflow-web/            ← projeto Next.js (ainda não criado)
```

### hireflow-api (Laravel)

```
hireflow-api/
├── app/
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
│   ├── (public)/            ← portal de candidatos
│   │   ├── jobs/
│   │   └── jobs/[id]/apply/
│   ├── (auth)/              ← login, register, OAuth callback
│   └── (dashboard)/         ← painel interno
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
**Credenciais:** definidas no `.env` do Docker (geradas com `openssl rand -base64 16`)

### Tabelas criadas (migrations rodadas)

| Tabela                   | Descrição                                                              |
| ------------------------ | ---------------------------------------------------------------------- |
| `users`                  | Usuários do sistema (com campos `provider` e `provider_id` para OAuth) |
| `password_reset_tokens`  | Tokens de reset de senha (padrão Laravel)                              |
| `sessions`               | Sessões ativas (padrão Laravel)                                        |
| `personal_access_tokens` | Tokens do Sanctum                                                      |
| `companies`              | Empresas que abrem vagas                                               |
| `roles`                  | Roles do sistema (admin, recruiter, hiring_manager, candidate)         |
| `user_roles`             | Pivot entre users e roles                                              |
| `job_openings`           | Vagas (renomeada de `jobs` por conflito com fila interna do Laravel)   |
| `job_stages`             | Etapas do pipeline de cada vaga                                        |
| `applications`           | Candidaturas                                                           |
| `application_stage_logs` | Histórico de movimentação no pipeline (auditoria)                      |
| `comments`               | Comentários internos por candidatura                                   |
| `notifications`          | Notificações in-app                                                    |

> **Atenção:** A tabela de vagas se chama `job_openings`, não `jobs`. O Laravel usa `jobs` internamente para filas.

### UUIDs

Todas as tabelas do domínio usam `uuid` como primary key, exceto `users` que usa `id` bigint padrão do Laravel. Isso será tratado nos Models com `HasUuids` trait.

---

## Roles & Permissões

Implementadas **na mão** (sem Spatie). As roles vivem na tabela `roles` com os slugs:

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

Toda vaga criada ganha estas etapas por padrão (customizáveis):

```
Triagem → Entrevista RH → Entrevista Técnica → Proposta → Contratado
```

Cada movimentação gera um registro em `application_stage_logs` e dispara uma notificação ao candidato.

---

## Autenticação

- **Email/senha** via Laravel Sanctum (tokens de API)
- **OAuth LinkedIn** via Laravel Socialite
- Guards no Next.js via `middleware.ts` protegendo rotas por role

---

## Pacotes instalados (hireflow-api)

| Pacote                   | Versão | Finalidade                 |
| ------------------------ | ------ | -------------------------- |
| `laravel/sanctum`        | ^4.3   | Auth via tokens            |
| `darkaonline/l5-swagger` | ^11.1  | Geração do Swagger/OpenAPI |
| `laravel/socialite`      | ^5.27  | OAuth LinkedIn             |

---

## Convenções do projeto

- **Commits:** Conventional Commits (`feat:`, `chore:`, `fix:`, `docs:`, etc.)
- **Idioma do código:** inglês (variáveis, métodos, tabelas)
- **Primary keys:** UUID em todas as tabelas do domínio
- **API:** REST, JSON, prefixo `/api/v1/`
- **Domínio local da API:** `hireflow-api.test` (Herd)

---

## Ordem de desenvolvimento

```
Infra (✓ concluída) → API → Front → Docs → DevOps/CI-CD
```

### Detalhamento do bloco API

1. ~~Configurar Herd~~ ✓
2. ~~Instalar dependências~~ ✓
3. ~~Migrations~~ ✓
4. Factories & Seeders ← **próximo passo**
5. Models + Relationships
6. Controllers + Routes + Requests
7. Policies
8. Swagger

---

## Estado atual

- Infra: **concluída**
- Docker: MySQL 8.0 + Redis 7-alpine rodando
- Herd: servindo `hireflow-api.test`
- Migrations: todas rodadas com `migrate:fresh`
- Repositório remoto: atualizado
- Próximo passo: **Factories & Seeders**
