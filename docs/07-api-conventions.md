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

| Método | Endpoint                  | Descrição                    | Auth    |
| ------ | ------------------------- | ---------------------------- | ------- |
| POST   | `/auth/register`          | Cadastro de candidato        | Público |
| POST   | `/auth/login`             | Login com email e senha      | Público |
| DELETE | `/auth/logout`            | Logout (invalida token)      | ✅      |
| GET    | `/auth/me`                | Dados do usuário autenticado | ✅      |
| GET    | `/auth/linkedin/redirect` | Inicia OAuth LinkedIn        | Público |
| GET    | `/auth/linkedin/callback` | Callback OAuth LinkedIn      | Público |

### Vagas

| Método | Endpoint                     | Descrição              | Auth             |
| ------ | ---------------------------- | ---------------------- | ---------------- |
| GET    | `/job-openings`              | Lista vagas publicadas | Público          |
| GET    | `/job-openings/{id}`         | Detalhe de uma vaga    | Público          |
| POST   | `/job-openings`              | Cria uma vaga          | Admin, Recruiter |
| PUT    | `/job-openings/{id}`         | Edita uma vaga         | Admin, Recruiter |
| PATCH  | `/job-openings/{id}/publish` | Publica uma vaga       | Admin, Recruiter |
| PATCH  | `/job-openings/{id}/close`   | Fecha uma vaga         | Admin, Recruiter |
| DELETE | `/job-openings/{id}`         | Remove uma vaga        | Admin            |

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
