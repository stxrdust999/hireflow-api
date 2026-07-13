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

| Método | Endpoint                          | Descrição                        | Auth                 |
| ------ | --------------------------------- | -------------------------------- | -------------------- |
| GET    | `/job-openings/{id}/applications` | Lista candidaturas de uma vaga   | Admin, Recruiter, HM |
| POST   | `/job-openings/{id}/applications` | Cria candidatura (inscrição)     | Candidate            |
| GET    | `/applications/{id}`              | Detalhe de uma candidatura       | Admin, Recruiter, HM |
| PATCH  | `/applications/{id}/stage`        | Move candidato de etapa          | Admin, Recruiter, HM |
| PATCH  | `/applications/{id}/withdraw`     | Candidato retira candidatura     | Candidate            |
| GET    | `/me/applications`                | Candidaturas do candidato logado | Candidate            |

### Comentários

| Método | Endpoint                      | Descrição           | Auth                 |
| ------ | ----------------------------- | ------------------- | -------------------- |
| GET    | `/applications/{id}/comments` | Lista comentários   | Admin, Recruiter, HM |
| POST   | `/applications/{id}/comments` | Adiciona comentário | Admin, Recruiter, HM |
| DELETE | `/comments/{id}`              | Remove comentário   | Admin, autor         |

### Notificações

| Método | Endpoint                   | Descrição                            | Auth |
| ------ | -------------------------- | ------------------------------------ | ---- |
| GET    | `/notifications`           | Lista notificações do usuário logado | ✅   |
| PATCH  | `/notifications/{id}/read` | Marca notificação como lida          | ✅   |
| PATCH  | `/notifications/read-all`  | Marca todas como lidas               | ✅   |

### Admin

| Método | Endpoint                  | Descrição                  | Auth  |
| ------ | ------------------------- | -------------------------- | ----- |
| GET    | `/admin/users`            | Lista usuários             | Admin |
| POST   | `/admin/users`            | Cria usuário interno       | Admin |
| PATCH  | `/admin/users/{id}/roles` | Atribui roles a um usuário | Admin |
| DELETE | `/admin/users/{id}`       | Remove usuário             | Admin |
| GET    | `/admin/companies`        | Lista empresas             | Admin |
| POST   | `/admin/companies`        | Cria empresa               | Admin |

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
