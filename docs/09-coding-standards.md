# 09 — Padrões de Código

## Visão geral

Este documento descreve as convenções adotadas no HireFlow para manter o código consistente, legível e fácil de manter — independentemente de quem esteja trabalhando no projeto.

---

## Idioma

| Contexto                                             | Idioma    |
| ---------------------------------------------------- | --------- |
| Código (variáveis, métodos, classes, tabelas, rotas) | Inglês    |
| Comentários no código                                | Português |
| Commits                                              | Inglês    |
| Documentação (`docs/`)                               | Português |

**Por quê inglês no código?**
Código em inglês é o padrão universal da indústria. Facilita leitura, integração com bibliotecas e eventual colaboração com pessoas de outros países.

**Por quê português nos comentários?**
Os comentários são anotações do desenvolvedor para si mesmo e para o time. Em português, a escrita é mais natural, rápida e expressiva.

---

## Commits — Conventional Commits

Todos os commits seguem o padrão [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/).

### Formato

```
<tipo>(<escopo opcional>): <descrição curta em inglês>
```

### Tipos utilizados

| Tipo       | Quando usar                                           |
| ---------- | ----------------------------------------------------- |
| `feat`     | Nova funcionalidade                                   |
| `fix`      | Correção de bug                                       |
| `chore`    | Tarefas de manutenção, instalação de pacotes, configs |
| `docs`     | Alterações na documentação                            |
| `refactor` | Refatoração sem mudança de comportamento              |
| `test`     | Adição ou modificação de testes                       |
| `style`    | Formatação, espaçamento — sem mudança de lógica       |
| `perf`     | Melhoria de performance                               |

### Exemplos

```bash
feat(auth): implement Sanctum token login endpoint
fix(applications): prevent duplicate applications for same job
chore: install and publish Sanctum and L5-Swagger
docs: update CLAUDE.md with models context
refactor(pipeline): extract stage movement logic to service class
```

_Pode ser que ainda tenham emojis nos commits tb hehe_

---

## PHP / Laravel

### Nomenclatura

| Elemento    | Convenção               | Exemplo                    |
| ----------- | ----------------------- | -------------------------- |
| Classes     | PascalCase              | `JobOpeningController`     |
| Métodos     | camelCase               | `moveToStage()`            |
| Variáveis   | camelCase               | `$currentStage`            |
| Tabelas     | snake_case, plural      | `job_openings`             |
| Colunas     | snake_case              | `created_by`, `resume_url` |
| Rotas       | kebab-case              | `/job-openings`            |
| Models      | singular, PascalCase    | `JobOpening`               |
| Controllers | singular + sufixo       | `JobOpeningController`     |
| Policies    | singular + sufixo       | `JobOpeningPolicy`         |
| Requests    | ação + recurso + sufixo | `StoreJobOpeningRequest`   |

### Estrutura de um Controller

Controllers são finos — não contêm lógica de negócio. Sua responsabilidade é receber a requisição, delegar para um Service e retornar a resposta.

```php
class JobOpeningController extends Controller
{
    public function __construct(
        private readonly JobOpeningService $service
    ) {}

    public function store(StoreJobOpeningRequest $request): JsonResponse
    {
        $jobOpening = $this->service->create(
            $request->validated(),
            $request->user()
        );

        return response()->json(['data' => $jobOpening], 201);
    }
}
```

### Services

A lógica de negócio fica nos Services, em `app/Services/`. Um Service por domínio.

```
app/Services/
├── JobOpeningService.php
├── ApplicationService.php
├── PipelineService.php
└── AuthService.php
```

### Form Requests

Toda validação de entrada usa Form Requests, em `app/Http/Requests/`. Nunca validar dentro do controller.

```php
class StoreJobOpeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['admin', 'recruiter']);
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type'        => ['required', 'in:full-time,part-time,contract,internship'],
            'location'    => ['nullable', 'string', 'max:255'],
            'company_id'  => ['required', 'uuid', 'exists:companies,id'],
        ];
    }
}
```

### Swagger — Anotações

Toda rota pública da API deve ser documentada com anotações OpenAPI diretamente no controller. O L5-Swagger lê essas anotações e gera o Swagger UI automaticamente.

🚧 _Padrão de anotação a ser definido durante a implementação dos controllers._

---

## TypeScript / Next.js

🚧 _Padrões a serem definidos quando o frontend for iniciado. Base prevista:_

### Nomenclatura

| Elemento               | Convenção                   | Exemplo               |
| ---------------------- | --------------------------- | --------------------- |
| Componentes            | PascalCase                  | `ApplicationCard`     |
| Hooks                  | camelCase com prefixo `use` | `useApplications`     |
| Funções utilitárias    | camelCase                   | `formatDate()`        |
| Constantes             | SCREAMING_SNAKE_CASE        | `MAX_FILE_SIZE`       |
| Arquivos de componente | PascalCase                  | `ApplicationCard.tsx` |
| Arquivos de utilitário | kebab-case                  | `format-date.ts`      |

### Clients HTTP (Orval)

Os clients HTTP **não são escritos manualmente**. O Orval os gera automaticamente a partir do Swagger da API. Os arquivos gerados ficam em `lib/api/` e não devem ser editados diretamente.

```bash
# Comando para regenerar os clients após mudanças na API
npx orval
```

---

## Estrutura de rotas da API

Todas as rotas ficam em `routes/api.php`, agrupadas por domínio e middleware:

```php
Route::prefix('v1')->group(function () {

    // Rotas públicas
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login',    [AuthController::class, 'login']);
    });

    Route::get('job-openings',      [JobOpeningController::class, 'index']);
    Route::get('job-openings/{id}', [JobOpeningController::class, 'show']);

    // Rotas autenticadas
    Route::middleware('auth:sanctum')->group(function () {

        Route::delete('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',        [AuthController::class, 'me']);

        // Rotas por role
        Route::middleware('role:admin,recruiter')->group(function () {
            Route::post('job-openings', [JobOpeningController::class, 'store']);
            // ...
        });

        Route::middleware('role:candidate')->group(function () {
            Route::post('job-openings/{id}/applications', [ApplicationController::class, 'store']);
            // ...
        });
    });

    // Rotas de admin
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        Route::get('users', [AdminUserController::class, 'index']);
        // ...
    });
});
```

_Ainda não foram iniciadas. Provavelmente vou separar rotas por arquivos, um por contexto. Por mais que seja desnecessário, funciona melhor na minha cabeça._

---

## Boas práticas gerais

- **Nunca commitar o `.env`** — o `.env.example` com valores vazios é o que vai para o repositório
- **Nunca colocar lógica de negócio no controller** — use Services
- **Nunca validar input no controller** — use Form Requests
- **Nunca editar arquivos gerados pelo Orval** — regenere-os
- **Sempre escrever o `down()` nas migrations** — permite reverter com `migrate:rollback`
- **Sempre usar `$table->cascadeOnDelete()`** nas FKs quando a deleção em cascata faz sentido semanticamente

Para entender como rodar o projeto do zero, veja [Setup de Desenvolvimento](./08-dev-setup.md).
