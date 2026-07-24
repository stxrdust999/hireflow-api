# 07 — Convenções da API

## Visão geral

A API do HireFlow segue os princípios REST e possui convenções consistentes em todos os endpoints — formato de resposta, tratamento de erros, paginação e versionamento. Conhecer essas convenções é essencial para consumir ou desenvolver novos endpoints.

---

## Base URL

| Ambiente | URL base                             |
| -------- | ------------------------------------ |
| Local    | `http://hireflow-api.test/api/v1`    |
| Produção | 🚧 `https://api.hireflow.app/api/v1` |

---

## Versionamento

Todos os endpoints são prefixados com `/api/v1/`. O versionamento na URL garante que mudanças breaking na API não quebrem clientes existentes — uma versão `/api/v2/` pode coexistir com a v1 durante uma transição.

---

## Autenticação

Endpoints protegidos exigem o token Sanctum no header:

```
Authorization: Bearer {token}
```

Endpoints públicos (ex: listagem de vagas) não exigem autenticação.

---

## Formato de resposta

Todas as respostas da API retornam JSON com uma estrutura consistente.

### Sucesso — recurso único

```json
{
    "data": {
        "id": "uuid",
        "title": "Desenvolvedor Backend Sênior",
        "status": "published",
        "created_at": "2024-03-15T14:32:00Z"
    }
}
```

### Sucesso — coleção

```json
{
    "data": [
        {
            "id": "uuid",
            "title": "Desenvolvedor Backend Sênior"
        },
        {
            "id": "uuid",
            "title": "Product Designer Pleno"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 42,
        "last_page": 3
    },
    "links": {
        "first": "http://hireflow-api.test/api/v1/job-openings?page=1",
        "last": "http://hireflow-api.test/api/v1/job-openings?page=3",
        "prev": null,
        "next": "http://hireflow-api.test/api/v1/job-openings?page=2"
    }
}
```

### Erro

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "title": ["The title field is required."],
        "type": ["The selected type is invalid."]
    }
}
```

---

## Códigos HTTP utilizados

| Código                      | Situação                                             |
| --------------------------- | ---------------------------------------------------- |
| `200 OK`                    | Requisição bem-sucedida (GET, PUT, PATCH)            |
| `201 Created`               | Recurso criado com sucesso (POST)                    |
| `204 No Content`            | Operação bem-sucedida sem corpo de resposta (DELETE) |
| `400 Bad Request`           | Requisição malformada                                |
| `401 Unauthorized`          | Token ausente ou inválido                            |
| `403 Forbidden`             | Autenticado, mas sem permissão para o recurso        |
| `404 Not Found`             | Recurso não encontrado                               |
| `422 Unprocessable Entity`  | Dados enviados falharam na validação                 |
| `500 Internal Server Error` | Erro inesperado no servidor                          |

---

## Paginação

Coleções são paginadas por padrão com **10 itens por página**. O número de itens por página pode ser ajustado via query param `per_page` (máximo: 50).

```
GET /api/v1/job-openings?page=2&per_page=25
```

---

## Filtros e ordenação

🚧 _Convenção definida — implementação pendente por endpoint._

Filtros são passados via query params:

```
GET /api/v1/job-openings?status=published&type=full-time
GET /api/v1/applications?stage=triagem&status=in_progress
```

Ordenação via `sort` e `direction`:

```
GET /api/v1/job-openings?sort=created_at&direction=desc
```

---

## Endpoints previstos

🚧 _Lista completa será gerada via Swagger. Endpoints abaixo são o planejamento inicial. Completamente suscetível a mudanças, nada definitivo ainda._

### Autenticação

| Método | Endpoint                  | Descrição                    | Auth    | Status          |
| ------ | ------------------------- | ---------------------------- | ------- | --------------- |
| POST   | `/auth/register`          | Cadastro de candidato        | Público | ✅ Implementado |
| POST   | `/auth/login`             | Login com email e senha      | Público | ✅ Implementado |
| DELETE | `/auth/logout`            | Logout (invalida token)      | ✅      | ✅ Implementado |
| GET    | `/auth/me`                | Dados do usuário autenticado | ✅      | ✅ Implementado |
| GET    | `/auth/linkedin/redirect` | Inicia OAuth LinkedIn        | Público | 🚧 Pendente     |
| GET    | `/auth/linkedin/callback` | Callback OAuth LinkedIn      | Público | 🚧 Pendente     |

**Formato de resposta do login** — token e usuário juntos, dentro do envelope `data`:

```json
{
    "data": {
        "token": "1|abc123...",
        "user": {
            "id": 1,
            "name": "Teste da Silva",
            "email": "teste@hireflow.com",
            "roles": ["candidate"],
            "is_active": true
        }
    }
}
```

**Códigos por endpoint:** `register` → `201`; `login` e `me` → `200`; `logout` → `204` (sem corpo). Requisição sem token em rota protegida → `401 {"message": "Unauthenticated."}`.

### Vagas

✅ _Módulo implementado — `JobOpeningController`, testado ponta a ponta via Postman._

| Método | Endpoint                     | Descrição              | Auth             | Status          |
| ------ | ---------------------------- | ---------------------- | ---------------- | --------------- |
| GET    | `/job-openings`              | Lista vagas publicadas | Público          | ✅ Implementado |
| GET    | `/job-openings/{id}`         | Detalhe de uma vaga    | Público          | ✅ Implementado |
| POST   | `/job-openings`              | Cria uma vaga          | Admin, Recruiter | ✅ Implementado |
| PUT    | `/job-openings/{id}`         | Edita uma vaga         | Admin, Recruiter | ✅ Implementado |
| PATCH  | `/job-openings/{id}/publish` | Publica uma vaga       | Admin, Recruiter | ✅ Implementado |
| PATCH  | `/job-openings/{id}/close`   | Fecha uma vaga         | Admin, Recruiter | ✅ Implementado |
| DELETE | `/job-openings/{id}`         | Remove uma vaga        | Admin            | ✅ Implementado |

> ✅ **Filtro de status aplicado:** o `index` retorna apenas vagas `published` (portal público não vê `draft`/`closed`).
>
> ⚠️ **Pendência conhecida:** paginação ainda não aplicada (`index` usa `->get()`, traz tudo). A implementar junto com filtros/ordenação por endpoint. Listagem interna com `draft`/`closed` (via `?status=`, role-aware) também fica pra esse bloco.

**Detalhes de implementação:**
- Route model binding por UUID nos endpoints com `{id}`.
- `created_by` (autoria) vem do usuário autenticado, não do corpo.
- `hiring_manager_ids` (opcional no POST/PUT) valida cada id via Rule `IsHiringManager` — precisa ser user com role `hiring-manager` (retorna `422` se não for).
- Status HTTP: `201` (POST), `200` (GET/PUT/PATCH), `204` (DELETE).

### Candidaturas

✅ _Módulo implementado — `ApplicationController`, testado ponta a ponta via Postman (incluindo os cenários de Policy)._

| Método | Endpoint                          | Descrição                        | Auth                 | Status          |
| ------ | --------------------------------- | -------------------------------- | -------------------- | --------------- |
| GET    | `/job-openings/{id}/applications` | Lista candidaturas de uma vaga   | Admin, Recruiter, HM | ✅ Implementado |
| POST   | `/job-openings/{id}/applications` | Cria candidatura (inscrição)     | Candidate            | ✅ Implementado |
| GET    | `/applications/{id}`              | Detalhe de uma candidatura       | Admin, Recruiter, HM | ✅ Implementado |
| PATCH  | `/applications/{id}/stage`        | Move candidato de etapa          | Admin, Recruiter, HM | ✅ Implementado |
| PATCH  | `/applications/{id}/withdraw`     | Candidato retira candidatura     | Candidate            | ✅ Implementado |
| GET    | `/me/applications`                | Candidaturas do candidato logado | Candidate            | ✅ Implementado |

**Detalhes de implementação:**

- **Autorização em duas camadas:** `role:` na rota (quem, por perfil) + Policy no Controller (sobre qual recurso). HM que não é responsável pela vaga recebe `403` mesmo tendo a role certa. Ver [Roles & Permissões](./05-roles-permissions.md#nível-de-recurso--laravel-policies).
- **Origem dos dados no POST:** `resume_url` vem do corpo; `candidate_id` do token; `job_id` da URL (rota aninhada) — nenhum dos dois últimos é aceito no corpo.
- **Candidatura duplicada é bloqueada** no `ApplicationService::apply` (mesmo `candidate_id` + `job_id`).
- **`PATCH /stage` exige `stage_id` no corpo** (`uuid`, deve existir em `job_stages`). Não há avanço automático — recruiter/HM escolhem a etapa livremente, inclusive retrocedendo. Mover para a última etapa do pipeline marca a candidatura como `approved`.
- **`withdraw` não deleta** — marca `status = withdrawn`, preservando o histórico em `application_stage_logs`.
- Status HTTP: `201` (POST), `200` (GET/PATCH), `204` (withdraw).

> ⚠️ **Pendência conhecida:** paginação não aplicada nas listagens (mesma pendência do módulo de vagas).

### Comentários

✅ _Módulo implementado — `CommentController`, testado ponta a ponta via Postman._

| Método | Endpoint                      | Descrição           | Auth                 | Status          |
| ------ | ----------------------------- | ------------------- | -------------------- | --------------- |
| GET    | `/applications/{id}/comments` | Lista comentários   | Admin, Recruiter, HM | ✅ Implementado |
| POST   | `/applications/{id}/comments` | Adiciona comentário | Admin, Recruiter, HM | ✅ Implementado |
| DELETE | `/comments/{id}`              | Remove comentário   | Admin, autor         | ✅ Implementado |

**Detalhes de implementação:**

- **`index`/`store` reutilizam a `ApplicationPolicy::view`** — não têm ability própria. Um HM só lê/escreve comentários de candidaturas das próprias vagas (a mesma pergunta "pode ver esta candidatura?" resolve as duas coisas).
- **`DELETE` usa a `CommentPolicy::delete`** — regra "admin **OU** autor". A role na rota libera as três roles internas; a Policy estreita para admin ou o autor do comentário.
- **Origem dos dados no POST:** só o texto vem do corpo, na chave `comment` (o `CommentService` grava na coluna `body`). `author_id` vem do token, `application_id` da URL.
- **Comentários são internos** — candidatos não têm acesso (barrados pela `role:` da rota).

### Notificações

✅ _Módulo implementado — `NotificationController`, testado ponta a ponta via Postman._

| Método | Endpoint                   | Descrição                            | Auth | Status          |
| ------ | -------------------------- | ------------------------------------ | ---- | --------------- |
| GET    | `/notifications`           | Lista notificações do usuário logado | ✅   | ✅ Implementado |
| PATCH  | `/notifications/{id}/read` | Marca notificação como lida          | ✅   | ✅ Implementado |
| PATCH  | `/notifications/read-all`  | Marca todas como lidas               | ✅   | ✅ Implementado |

**Detalhes de implementação:**

- **Único módulo sem `role:` nas rotas** — notificação não é sobre perfil: todo usuário autenticado tem as suas. As rotas levam apenas `auth:sanctum`.
- **`index` e `read-all` são escopados** (sem Policy): o `index` filtra `where('user_id', Auth::id())` e o Service do `read-all` já recebe o usuário e filtra internamente.
- **`{id}/read` usa a `NotificationPolicy::read`** — é o único que toca um recurso específico por id, então precisa de checagem de posse.
- **Resposta expõe `is_read`** (booleano derivado de `read_at`) além do `read_at` em si. `user_id` não é exposto — o endpoint já é escopado no usuário autenticado.
- Status HTTP: `200` (GET e `{id}/read`, que devolve a notificação atualizada), `204` (`read-all`, sem corpo).

### Empresas

| Método | Endpoint             | Descrição                          | Auth    | Status          |
| ------ | -------------------- | ---------------------------------- | ------- | --------------- |
| GET    | `/companies`         | Lista empresas ativas              | Público | ✅ Implementado |
| GET    | `/companies/{slug}`  | Detalhe + vagas publicadas         | Público | ✅ Implementado |
| POST   | `/companies`         | Cria empresa                       | Admin   | ✅ Implementado |
| PUT    | `/companies/{id}`    | Edita empresa (parcial)            | Admin   | ✅ Implementado |
| DELETE | `/companies/{id}`    | Desativa empresa (não deleta)      | Admin   | ✅ Implementado |

**Decisões:**

- **Recurso público, não sob `/admin`.** O planejamento inicial previa `/admin/companies`, mas `index` e `show` são públicos: o portal de candidatos vai ter uma página por empresa (`/empresas/{slug}`) listando as vagas dela. Só a escrita (`store`/`update`/`destroy`) exige `role:admin`.
- **`show` busca por `slug`, escrita usa UUID.** O slug é a chave pública legível; o UUID fica nas rotas de gestão. O `show` não usa route model binding — faz busca explícita com `where('is_active', true)->firstOrFail()`, para que empresa desativada devolva `404`. Com binding implícito o registro continuaria acessível por URL mesmo fora da listagem.
- **`show` traz só vagas publicadas**, via _constrained eager loading_ (`load(['jobOpenings' => fn($q) => $q->where('status', Published)])`). Rascunhos e vagas fechadas não vazam pro público — e nem são trazidos do banco. O filtro é do Controller, não do Resource: Resource formata, não filtra.
- **Sem Policy.** Empresa não tem dono — não existe "este admin pode editar esta empresa mas não aquela". O `role:admin` na rota já é a decisão inteira, fonte única. Segue a regra fechada no módulo Notification: Policy só quando a autorização depende do recurso, não só do perfil.
- **`slug` não é aceito no corpo** de nenhuma das Requests — é derivado do `name` no Service (ver `03-database.md`). Mandar `"slug"` no POST é silenciosamente descartado pelo `validated()`.
- **Resposta usa `whenLoaded` em `job_openings`:** o campo só existe no JSON do `show`. No `index` a relação não é carregada e a chave some — listagem enxuta, sem N+1.
- Status HTTP: `201` (store), `200` (index/show/update), `204` (destroy, sem corpo), `404` (empresa inativa no show).

### Admin

| Método | Endpoint                  | Descrição                  | Auth  | Status       |
| ------ | ------------------------- | -------------------------- | ----- | ------------ |
| GET    | `/admin/users`            | Lista usuários             | Admin | 🚧 Pendente  |
| POST   | `/admin/users`            | Cria usuário interno       | Admin | 🚧 Pendente  |
| PATCH  | `/admin/users/{id}/roles` | Atribui roles a um usuário | Admin | 🚧 Pendente  |
| DELETE | `/admin/users/{id}`       | Desativa usuário           | Admin | 🚧 Pendente  |

---

## Nomenclatura

- URLs em **kebab-case**: `/job-openings`, `/hiring-managers`
- Substantivos no plural para coleções: `/applications`, não `/application`
- Verbos apenas em ações que não se encaixam em CRUD: `/publish`, `/close`, `/withdraw`

---

## CORS

🚧 _Configuração pendente — bloco de DevOps._

Somente requisições originadas do domínio do `hireflow-web` serão aceitas em produção.

Para detalhes sobre como a documentação Swagger é gerada a partir desses endpoints, veja [Padrões de Código](./09-coding-standards.md).
