# 02 — Arquitetura

## Visão geral

O HireFlow é composto por dois sistemas completamente independentes que se comunicam exclusivamente via HTTP. Não há compartilhamento de código, banco de dados ou qualquer recurso entre eles.

```
┌─────────────────────────────┐         ┌─────────────────────────────┐
│        hireflow-web         │         │        hireflow-api         │
│         (Next.js)           │──HTTP──▶│         (Laravel)           │
│                             │◀──JSON──│                             │
└─────────────────────────────┘         └──────────────┬──────────────┘
                                                       │
                                          ┌────────────┴────────────┐
                                          │                         │
                                    ┌─────▼──────┐         ┌───────▼──────┐
                                    │   MySQL    │         │    Redis     │
                                    │   8.0      │         │  7-alpine    │
                                    └────────────┘         └──────────────┘
```

---

## Por que dois projetos separados?

Essa decisão foi intencional e reflete como sistemas são organizados em ambientes profissionais reais, onde front-end e back-end frequentemente são mantidos por equipes distintas, em repositórios distintos.

As consequências práticas dessa separação são:

- O front-end depende **exclusivamente do contrato da API** (documentado via Swagger) para saber o que pode consumir — sem atalhos
- Cada projeto pode evoluir, ser deployado e escalado de forma independente
- Não há risco de lógica de negócio vazar para o front-end ou vice-versa
- O back-end pode ser consumido futuramente por outros clientes (app mobile, integrações externas) sem alterações

---

## hireflow-api (Laravel)

Responsável por toda a lógica de negócio, persistência de dados e autenticação. Expõe uma API REST versionada sob o prefixo `/api/v1/`.

**Responsabilidades:**
- Autenticação e autorização (Sanctum + Socialite)
- CRUD de vagas, candidaturas, empresas e usuários
- Gerenciamento do pipeline de seleção
- Envio de notificações e e-mails (via filas no Redis)
- Cache de listagens pesadas (Redis)
- Geração da documentação Swagger (L5-Swagger)

**Ambiente local:**
- Servido pelo Laravel Herd via Nginx
- Domínio: `hireflow-api.test`
- Banco e cache via Docker

---

## hireflow-web (Next.js)

🚧 *Em construção — não iniciado.*

Responsável pela interface do usuário. Consome a API do Laravel via clients HTTP gerados automaticamente pelo Orval a partir do Swagger.

**Responsabilidades:**
- Portal público de vagas (acessível sem login)
- Portal de candidatos (login obrigatório — role `candidate`)
- Painel interno (login obrigatório — roles `admin`, `recruiter`, `hiring_manager`)
- Proteção de rotas por role via `middleware.ts`
- Geração automática de clients HTTP tipados via Orval

**Por que Orval?**
O Orval lê o arquivo Swagger gerado pela API e produz automaticamente funções TypeScript tipadas para cada endpoint. Isso elimina a necessidade de escrever fetch/axios manualmente e garante que o front-end esteja sempre sincronizado com o contrato da API.

---

## Infraestrutura local

Ambos os serviços de dados rodam via Docker, isolados do ambiente host.

| Serviço | Imagem | Porta | Finalidade |
|---|---|---|---|
| MySQL | `mysql:8.0` | `3306` | Persistência principal |
| Redis | `redis:7-alpine` | `6379` | Cache + filas de jobs |

O `docker-compose.yml` fica dentro de `hireflow-api/`. As credenciais de acesso são geradas com `openssl rand -base64 16` e armazenadas no `.env` do Laravel.

**Por que Redis para filas?**
O Laravel possui um sistema de filas nativo que pode usar diferentes drivers. O Redis é o driver recomendado para produção por ser em memória (rápido) e persistente. No HireFlow, as filas são usadas para processar o envio de e-mails e notificações de forma assíncrona — sem bloquear a resposta da API enquanto o e-mail é enviado.

---

## Fluxo de uma requisição

Para ilustrar como os sistemas interagem, segue o fluxo completo de uma candidatura:

```
Candidato clica em "Me candidatar"
        │
        ▼
hireflow-web envia POST /api/v1/applications
        │
        ▼
hireflow-api valida o token Sanctum (autenticação)
        │
        ▼
hireflow-api verifica se o usuário tem role "candidate" (autorização)
        │
        ▼
hireflow-api persiste a candidatura no MySQL
        │
        ▼
hireflow-api enfileira uma notificação no Redis
        │
        ▼
hireflow-api retorna 201 Created com os dados da candidatura
        │
        ▼
hireflow-web exibe confirmação ao candidato
        │
        ▼ (assíncrono, em background)
Worker do Laravel processa a fila e envia e-mail ao candidato
```

---

## Estrutura de pastas

### hireflow-api

```
hireflow-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/    ← controllers separados por domínio
│   │   │   ├── Auth/
│   │   │   ├── Jobs/
│   │   │   ├── Applications/
│   │   │   └── Admin/
│   │   ├── Middleware/         ← middlewares customizados (ex: CheckRole)
│   │   └── Requests/           ← validação de entrada (Form Requests)
│   ├── Models/                 ← Eloquent models
│   ├── Policies/               ← regras de autorização por recurso
│   ├── Services/               ← lógica de negócio desacoplada dos controllers
│   └── Notifications/          ← classes de notificação (e-mail, in-app)
├── database/
│   ├── migrations/             ← estrutura do banco
│   ├── factories/              ← geração de dados falsos para testes/seeds
│   └── seeders/                ← população inicial do banco
├── routes/
│   └── api.php                 ← todas as rotas da API
└── storage/app/resumes/        ← currículos enviados pelos candidatos
```

### hireflow-web

🚧 *Em construção — estrutura definida, implementação não iniciada.*

```
hireflow-web/
├── app/
│   ├── (public)/               ← rotas acessíveis sem login
│   ├── (auth)/                 ← login, cadastro, OAuth callback
│   └── (dashboard)/            ← painel interno protegido por role
├── components/                 ← componentes reutilizáveis
├── lib/
│   └── api/                    ← clients HTTP gerados pelo Orval
└── middleware.ts                ← proteção de rotas por role
```

---

## Decisões de design relevantes

| Decisão | Motivo |
|---|---|
| API versionada (`/api/v1/`) | Permite evoluir a API sem quebrar clientes existentes |
| UUID como PK nas tabelas de domínio | Evita exposição de IDs sequenciais, mais seguro em APIs públicas |
| `users` com PK bigint | Compatibilidade com Sanctum e helpers nativos do Laravel (`foreignId`) |
| Tabela `job_openings` em vez de `jobs` | O Laravel reserva a tabela `jobs` internamente para o sistema de filas |
| Roles implementadas manualmente | Controle total sobre a lógica — Spatie Laravel Permission foi descartado intencionalmente |

Para detalhes sobre o banco de dados, veja [Banco de Dados](./03-database.md).  
Para detalhes sobre autenticação, veja [Autenticação](./04-auth.md).
