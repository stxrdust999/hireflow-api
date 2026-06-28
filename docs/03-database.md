# 03 — Banco de Dados

## Visão geral

O HireFlow utiliza **MySQL 8.0** como banco de dados principal, rodando via Docker. A modelagem segue os princípios de um banco relacional normalizado, com chaves estrangeiras explícitas e auditoria de movimentações embutida no design.

---

## Decisões de design

### UUIDs como chave primária

Todas as tabelas do domínio utilizam `uuid` como chave primária, com exceção de `users`.

**Por quê?**
IDs sequenciais (`1, 2, 3...`) em APIs públicas expõem informações sobre o volume de dados do sistema e são trivialmente enumeráveis — qualquer pessoa pode tentar acessar `/api/v1/applications/1`, `/api/v1/applications/2`, e assim por diante. UUIDs eliminam esse problema.

**Por que `users` é exceção?**
A tabela `users` mantém o `bigint` auto-increment padrão do Laravel por compatibilidade com o Sanctum (que cria sua tabela `personal_access_tokens` esperando uma FK para `users.id` como inteiro) e com o helper `foreignId()` do Laravel, amplamente usado nas migrations.

### Tabela `job_openings` em vez de `jobs`

O Laravel reserva a tabela `jobs` internamente para o sistema de filas (Queue). Nomear nossa tabela de vagas como `jobs` causaria conflito direto. O nome `job_openings` (vagas abertas) é semanticamente mais preciso para o domínio de recrutamento.

### Roles implementadas no banco

As roles não são hardcoded no código — vivem na tabela `roles` e são associadas a usuários via `user_roles`. Isso permite que um administrador gerencie roles sem deploys, e que o sistema evolua com novas roles sem mudanças estruturais.

### `application_stage_logs` como tabela de auditoria

Toda movimentação de um candidato no pipeline gera um registro imutável nessa tabela. Isso garante rastreabilidade completa: é possível saber exatamente quem moveu quem, de qual etapa para qual, e quando — sem depender de logs de servidor.

---

## Diagrama de entidades (ERD)

```
users ──────────────────────────────────────────────────────────┐
  │ id (bigint)                                                  │
  │ name                                                         │
  │ email                                                        │
  │ password_hash                                                │
  │ provider (nullable)        ← 'linkedin' ou null             │
  │ provider_id (nullable)     ← ID retornado pelo OAuth        │
  │ email_verified_at                                            │
  └──────┬──────────────────────────────────────────────────────┘
         │                                                       │
         │ N:N via user_roles                                    │ 1:N
         ▼                                                       ▼
       roles                                              job_openings
         id (uuid)                                          id (uuid)
         name                                               company_id ──▶ companies
         slug                                               created_by ──▶ users
                                                            title
                                                            description
                                                            location
                                                            type (enum)
                                                            status (enum)
                                                                │
                                                                │ 1:N
                                                                ▼
                                                          job_stages
                                                            id (uuid)
                                                            job_id
                                                            name
                                                            order (int)
                                                                │
                                                    ┌───────────┘
                                                    │
         users ──────────────────────────── applications
                                              id (uuid)
                                              job_id ──────────▶ job_openings
                                              candidate_id ────▶ users
                                              current_stage_id ▶ job_stages
                                              resume_url
                                              status (enum)
                                                    │
                                    ┌───────────────┼───────────────┐
                                    │               │               │
                                    ▼               ▼               ▼
                         application_stage_logs  comments     notifications
                           id (uuid)              id (uuid)     id (uuid)
                           application_id         application_id user_id
                           stage_id               author_id     type
                           moved_by ──▶ users     body          data (json)
                           moved_at                             read_at
```

---

## Tabelas

### `users`

Usuários do sistema. Engloba todos os tipos — candidatos, recrutadores, admins. O tipo é determinado pela role associada, não por colunas separadas.

| Coluna                      | Tipo                | Descrição                                   |
| --------------------------- | ------------------- | ------------------------------------------- |
| `id`                        | bigint, PK          | Auto-increment padrão do Laravel            |
| `name`                      | string              | Nome completo                               |
| `email`                     | string, unique      | E-mail de acesso                            |
| `password`                  | string, nullable    | Hash da senha. Nullable para usuários OAuth |
| `provider`                  | string, nullable    | Provedor OAuth utilizado (`linkedin`)       |
| `provider_id`               | string, nullable    | ID do usuário no provedor OAuth             |
| `email_verified_at`         | timestamp, nullable | Data de verificação do e-mail               |
| `remember_token`            | string, nullable    | Token de "lembrar sessão"                   |
| `created_at` / `updated_at` | timestamp           | Gerenciados pelo Laravel                    |

---

### `roles`

Papéis disponíveis no sistema. Populada via Seeder na instalação.

| Coluna                      | Tipo           | Descrição                                              |
| --------------------------- | -------------- | ------------------------------------------------------ |
| `id`                        | uuid, PK       | —                                                      |
| `name`                      | string         | Nome legível (`Admin`, `Recruiter`)                    |
| `slug`                      | string, unique | Identificador programático (`admin`, `hiring-manager`) |
| `created_at` / `updated_at` | timestamp      | —                                                      |

---

### `user_roles`

Tabela pivot da relação N:N entre usuários e roles. Um usuário pode ter múltiplas roles.

| Coluna                      | Tipo               | Descrição |
| --------------------------- | ------------------ | --------- |
| `user_id`                   | bigint, FK → users | —         |
| `role_id`                   | uuid, FK → roles   | —         |
| `created_at` / `updated_at` | timestamp          | —         |

_PK composta: (`user_id`, `role_id`)_

---

### `companies`

Empresas que publicam vagas no sistema.

| Coluna                      | Tipo             | Descrição                             |
| --------------------------- | ---------------- | ------------------------------------- |
| `id`                        | uuid, PK         | —                                     |
| `name`                      | string           | Nome da empresa                       |
| `slug`                      | string, unique   | Identificador para URLs (`acme-corp`) |
| `logo_url`                  | string, nullable | URL do logotipo                       |
| `created_at` / `updated_at` | timestamp        | —                                     |

---

### `job_openings`

Vagas abertas pelas empresas. O status controla a visibilidade no portal público.

| Coluna                      | Tipo                 | Descrição                                          |
| --------------------------- | -------------------- | -------------------------------------------------- |
| `id`                        | uuid, PK             | —                                                  |
| `company_id`                | uuid, FK → companies | Empresa dona da vaga                               |
| `created_by`                | bigint, FK → users   | Recrutador que criou                               |
| `title`                     | string               | Título da vaga                                     |
| `description`               | longtext             | Descrição completa                                 |
| `location`                  | string, nullable     | Localização ou "Remoto"                            |
| `type`                      | enum                 | `full-time`, `part-time`, `contract`, `internship` |
| `status`                    | enum                 | `draft`, `published`, `closed`                     |
| `created_at` / `updated_at` | timestamp            | —                                                  |

---

### `job_stages`

Etapas do pipeline de cada vaga. Cada vaga tem suas próprias etapas, criadas com valores padrão ao publicar.

| Coluna                      | Tipo                    | Descrição                                  |
| --------------------------- | ----------------------- | ------------------------------------------ |
| `id`                        | uuid, PK                | —                                          |
| `job_id`                    | uuid, FK → job_openings | Vaga à qual a etapa pertence               |
| `name`                      | string                  | Nome da etapa (`Triagem`, `Entrevista RH`) |
| `order`                     | unsignedInteger         | Posição no pipeline (1, 2, 3...)           |
| `created_at` / `updated_at` | timestamp               | —                                          |

**Etapas padrão criadas para toda nova vaga:**

| Order | Nome (código)      | Descrição               |
| ----- | ------------------ | ----------------------- |
| 1     | `screening`        | Triagem                 |
| 2     | `hr-interview`     | Entrevista RH           |
| 3     | `technical-interview` | Entrevista Técnica   |
| 4     | `offer`            | Proposta                |
| 5     | `hired`            | Contratado              |

> Os nomes em inglês são usados no banco e no código; as descrições em português são exibidas na interface do usuário.

---

### `applications`

Candidaturas de usuários a vagas. Uma mesma pessoa não pode se candidatar duas vezes à mesma vaga.

| Coluna                      | Tipo                            | Descrição                                                     |
| --------------------------- | ------------------------------- | ------------------------------------------------------------- |
| `id`                        | uuid, PK                        | —                                                             |
| `job_id`                    | uuid, FK → job_openings         | Vaga em questão                                               |
| `candidate_id`              | bigint, FK → users              | Candidato                                                     |
| `current_stage_id`          | uuid, FK → job_stages, nullable | Etapa atual no pipeline                                       |
| `resume_url`                | string, nullable                | Caminho do currículo enviado                                  |
| `status`                    | enum                            | `pending`, `in_progress`, `approved`, `rejected`, `withdrawn` |
| `created_at` / `updated_at` | timestamp                       | —                                                             |

---

### `application_stage_logs`

Histórico imutável de cada movimentação no pipeline. Nunca é deletado.

| Coluna                      | Tipo                    | Descrição                     |
| --------------------------- | ----------------------- | ----------------------------- |
| `id`                        | uuid, PK                | —                             |
| `application_id`            | uuid, FK → applications | Candidatura movida            |
| `stage_id`                  | uuid, FK → job_stages   | Etapa para a qual foi movida  |
| `moved_by`                  | bigint, FK → users      | Quem realizou a movimentação  |
| `moved_at`                  | timestamp               | Momento exato da movimentação |
| `created_at` / `updated_at` | timestamp               | —                             |

---

### `comments`

Comentários internos sobre uma candidatura. Visíveis apenas para recrutadores, hiring managers e admins — nunca para o candidato.

| Coluna                      | Tipo                    | Descrição              |
| --------------------------- | ----------------------- | ---------------------- |
| `id`                        | uuid, PK                | —                      |
| `application_id`            | uuid, FK → applications | Candidatura comentada  |
| `author_id`                 | bigint, FK → users      | Autor do comentário    |
| `body`                      | text                    | Conteúdo do comentário |
| `created_at` / `updated_at` | timestamp               | —                      |

---

### `notifications`

Notificações in-app dos usuários. Armazena o tipo e um payload JSON flexível que varia conforme o tipo de notificação.

| Coluna                      | Tipo                | Descrição                                                            |
| --------------------------- | ------------------- | -------------------------------------------------------------------- |
| `id`                        | uuid, PK            | —                                                                    |
| `user_id`                   | bigint, FK → users  | Destinatário                                                         |
| `type`                      | string              | Tipo da notificação (`application_advanced`, `application_rejected`) |
| `data`                      | json                | Payload variável conforme o tipo                                     |
| `read_at`                   | timestamp, nullable | Null = não lida                                                      |
| `created_at` / `updated_at` | timestamp           | —                                                                    |

**Tipos de notificações usados no sistema**

| Evento                                | Tipo                    |
| ------------------------------------- | ----------------------- |
| Candidatura recebida                  | `application_received`  |
| Avanço de etapa                       | `application_advanced`  |
| Candidatura reprovada                 | `application_rejected`  |
| Proposta enviada                      | `application_proposal`  |
| Contratação confirmada                | `application_hired`     |
| Candidato retirou candidatura         | `application_withdrawn` |
| Encerramento formal do processo       | `application_closed`    |
| Convite para entrevista 🚧            | `interview_scheduled`   |
| Vaga salva próxima do encerramento 🚧 | `job_deadline_near`     |
| Candidatos aguardando avaliação       | `stage_pending_review`  |

**Estrutura do campo `data` por tipo de notificação**

| Tipo                     | Payload (`data`)                                                                                    |
| ------------------------ | --------------------------------------------------------------------------------------------------- |
| `application_received`   | `{ "job_id": "uuid", "job_title": "string" }`                                                       |
| `application_advanced`   | `{ "job_id": "uuid", "job_title": "string", "stage_name": "string", "application_id": "uuid" }`     |
| `application_rejected`   | `{ "job_id": "uuid", "job_title": "string", "application_id": "uuid" }`                             |
| `application_proposal`   | `{ "job_id": "uuid", "job_title": "string", "application_id": "uuid" }`                             |
| `application_hired`      | `{ "job_id": "uuid", "job_title": "string", "application_id": "uuid" }`                             |
| `application_withdrawn`  | `{ "job_id": "uuid", "job_title": "string", "candidate_name": "string", "application_id": "uuid" }` |
| `application_closed`     | `{ "job_id": "uuid", "job_title": "string", "application_id": "uuid" }`                             |
| `interview_scheduled` 🚧 | `{ "job_id": "uuid", "job_title": "string", "application_id": "uuid", "scheduled_at": "datetime" }` |
| `job_deadline_near` 🚧   | `{ "job_id": "uuid", "job_title": "string", "closes_at": "datetime" }`                              |
| `stage_pending_review`   | `{ "job_id": "uuid", "job_title": "string", "stage_name": "string", "pending_count": "int" }`       |

---

### `personal_access_tokens`

Gerada automaticamente pelo Sanctum ao publicar o provider. Armazena os tokens de API dos usuários autenticados.

> Não manipulada diretamente — gerenciada pelo Sanctum.

---

## Comportamento em cascata

| Ação                  | Consequência                                                                 |
| --------------------- | ---------------------------------------------------------------------------- |
| Deletar `company`     | Deleta todas as `job_openings` associadas em cascata                         |
| Deletar `job_opening` | Deleta `job_stages` e `applications` em cascata                              |
| Deletar `application` | Deleta `application_stage_logs` e `comments` em cascata                      |
| Deletar `user`        | Deleta `user_roles`, `applications`, `comments` e `notifications` em cascata |
| Deletar `job_stage`   | `applications.current_stage_id` vira `null` (nullOnDelete)                   |

Para detalhes sobre autenticação e como os usuários acessam esses dados, veja [Autenticação](./04-auth.md).

---

## Factories & Seeders

### Factories

Todas em `database/factories/`. Ligadas aos models via trait `HasFactory`.

| Factory | O que gera |
|---|---|
| `UserFactory` | Nome, email, senha (`password`), email verificado |
| `RoleFactory` | Nome e slug aleatórios |
| `CompanyFactory` | Nome único, slug, logo_url |
| `JobOpeningFactory` | Título, descrição, local, type, status. Cria company e user aninhados |
| `JobStageFactory` | Nome e order |
| `ApplicationFactory` | Cria candidate, job e current_stage aninhados |
| `ApplicationStageLogFactory` | Cria application, stage e moved_by aninhados |
| `CommentFactory` | Cria application e author aninhados, body textual |
| `NotificationFactory` | 10 tipos de notificação com payload JSON consistente |

A `NotificationFactory` é a mais complexa: seu método `generateNotificationData()` constrói o payload `data` conforme o tipo — `application_received`, `application_advanced`, `application_rejected`, `application_proposal`, `application_hired`, `application_withdrawn`, `application_closed`, `interview_scheduled`, `job_deadline_near`, `stage_pending_review`.

### Seeders

Orquestrados pelo `DatabaseSeeder` na ordem:

```
RoleSeeder → UserSeeder → CompanySeeder → JobOpeningSeeder → JobStageSeeder
→ ApplicationSeeder → ApplicationStageLogSeeder → CommentSeeder → NotificationSeeder
```

O que cada seeder produz:

| Seeder | O que cria |
|---|---|
| `RoleSeeder` | 4 roles: admin, recruiter, hiring-manager, candidate |
| `UserSeeder` | 1 admin (credenciais do `.env` via `config/services.php`), 5 recruiters, 5 HMs, 20 candidates |
| `CompanySeeder` | Empresas aleatórias via factory |
| `JobOpeningSeeder` | 20 vagas vinculadas a empresas e recruiters existentes |
| `JobStageSeeder` | 5 etapas por vaga: `screening`, `hr-interview`, `technical-interview`, `offer`, `hired` |
| `ApplicationSeeder` | Candidaturas distribuindo candidates pelas vagas |
| `ApplicationStageLogSeeder` | Histórico de movimentações simuladas |
| `CommentSeeder` | Comentários internos de recrutadores/HMs |
| `NotificationSeeder` | Notificações mockadas para os candidatos |

### Como executar

```bash
# Do zero
php artisan migrate:fresh --seed

# Apenas seeders
php artisan db:seed

# Seeder específico
php artisan db:seed --class=RoleSeeder
```
