# 05 — Roles & Permissões

## Visão geral

O HireFlow utiliza um sistema de controle de acesso baseado em roles (RBAC — Role-Based Access Control). Cada usuário possui uma ou mais roles, e cada role define o que aquele usuário pode ou não fazer no sistema.

---

## Decisão de implementação

O sistema de roles foi implementado **manualmente**, sem o uso de pacotes externos como o Spatie Laravel Permission. Essa decisão foi intencional: o objetivo é ter controle total e visibilidade completa sobre cada camada do sistema de permissões, sem abstrações que escondam o funcionamento interno.

O que isso significa na prática:

- As roles vivem na tabela `roles` no banco de dados
- A associação entre usuários e roles é feita via tabela `user_roles`
- A verificação de permissões é feita via **Laravel Policies**, nativas do framework
- Um middleware customizado `CheckRole` protege as rotas por role

---

## Vínculo entre Hiring Manager e vaga

A regra "Hiring Manager só vê/move candidatos das suas próprias vagas" depende de saber quais vagas são "suas". Isso é resolvido por uma tabela pivot dedicada — `job_opening_hiring_managers` — que associa vagas a Hiring Managers em relação N:N (uma vaga pode ter vários HMs responsáveis; um HM pode estar em várias vagas).

- `JobOpening::hiringManagers()` — HMs responsáveis por aquela vaga
- `User::managedJobs()` — vagas sob responsabilidade daquele HM

A atribuição acontece de duas formas: opcionalmente já na criação da vaga (`JobOpeningService::create` aceita `hiring_manager_ids`), ou depois via `assignHiringManager()` / `removeHiringManager()` — útil enquanto a vaga ainda está em `draft` ou quando o HM responsável muda.

---

## Roles disponíveis

| Role | Slug | Descrição |
|---|---|---|
| Admin | `admin` | Controle total do sistema. Gerencia usuários, roles e configurações globais. |
| Recruiter | `recruiter` | Profissional de RH. Cria vagas, gerencia candidaturas e conduz o pipeline. |
| Hiring Manager | `hiring-manager` | Gestor da área contratante. Avalia candidatos nas vagas sob sua responsabilidade. |
| Candidate | `candidate` | Usuário externo. Busca vagas, se candidata e acompanha suas candidaturas. |

---

## Matriz de permissões

| Ação | Admin | Recruiter | Hiring Manager | Candidate |
|---|---|---|---|---|
| Gerenciar usuários & roles | ✅ | — | — | — |
| Criar vagas | ✅ | ✅ | — | — |
| Editar vagas | ✅ | ✅ | — | — |
| Publicar / fechar vagas | ✅ | ✅ | — | — |
| Ver todas as candidaturas | ✅ | ✅ | — | — |
| Ver candidaturas das próprias vagas | ✅ | ✅ | ✅ | — |
| Mover candidato no pipeline | ✅ | ✅ | só suas vagas | — |
| Adicionar comentários internos | ✅ | ✅ | ✅ | — |
| Ver dashboard de métricas completo | ✅ | ✅ | — | — |
| Ver métricas das próprias vagas | ✅ | ✅ | ✅ | — |
| Configurações gerais do sistema | ✅ | — | — | — |
| Buscar vagas publicadas | ✅ | ✅ | ✅ | ✅ |
| Se candidatar a vagas | — | — | — | ✅ |
| Ver status das próprias candidaturas | — | — | — | ✅ |
| Retirar própria candidatura | — | — | — | ✅ |

---

## Como as permissões são verificadas

### Nível de rota — Middleware `CheckRole`

✅ *Implementado — `app/Http/Middleware/CheckRole.php`, alias `role` registrado no `bootstrap/app.php`.*

O middleware `CheckRole` é a primeira linha de defesa. Ele impede que um usuário sem a role adequada sequer chegue ao controller.

As roles exigidas são passadas como parâmetros variádicos na rota (`role:admin,recruiter` → `handle($request, $next, 'admin', 'recruiter')`). Internamente, delega a verificação ao `User::hasRole()`. Deve ser sempre encadeado **após** `auth:sanctum` — depende do usuário já estar autenticado. Se `$request->user()` for nulo ou não tiver nenhuma das roles, aborta com `403`.

```php
// Exemplo de uso nas rotas
Route::middleware(['auth:sanctum', 'role:recruiter,admin'])->group(function () {
    Route::post('/job-openings', [JobOpeningController::class, 'store']);
    Route::put('/job-openings/{id}', [JobOpeningController::class, 'update']);
});
```

O middleware verifica se o usuário autenticado possui **pelo menos uma** das roles listadas. Se não possuir, retorna `403 Forbidden`.

### Nível de recurso — Laravel Policies

🚧 *Implementação pendente.*

Enquanto o middleware verifica se o usuário tem a role certa, as Policies verificam se o usuário tem permissão sobre **aquele recurso específico**. Esse é o mecanismo que implementa regras como "Hiring Manager só vê candidaturas das suas próprias vagas".

```php
// Exemplo conceitual — JobOpeningPolicy
public function update(User $user, JobOpening $jobOpening): bool
{
    // Admin e Recruiter podem editar qualquer vaga
    if ($user->hasRole('admin') || $user->hasRole('recruiter')) {
        return true;
    }

    // Hiring Manager só pode interagir com vagas onde foi designado
    return false;
}

// Exemplo conceitual — ApplicationPolicy
public function moveStage(User $user, Application $application): bool
{
    if ($user->hasRole('admin') || $user->hasRole('recruiter')) {
        return true;
    }

    // Hiring Manager só move candidatos em vagas às quais está vinculado
    if ($user->hasRole('hiring-manager')) {
        return $application->job->hiringManagers->contains($user->id);
    }

    return false;
}
```

### Nível de model — Método auxiliar `hasRole()`

Para facilitar as verificações nas Policies e em qualquer outro ponto do código, o model `User` expõe um método `hasRole()`:

✅ *Implementado — `app/Models/User.php`.*

```php
$user->hasRole('admin');                // true/false
$user->hasRole(['admin', 'recruiter']); // true se tiver qualquer uma das duas
```

Implementação: normaliza o parâmetro para array (`(array) $roles`), extrai os slugs das roles do usuário via `pluck('slug')` e retorna se a interseção entre os dois conjuntos não é vazia (lógica OR).

---

## Hierarquia e múltiplas roles

Um usuário pode ter múltiplas roles simultaneamente. Exemplos de cenários válidos:

- Um Admin que também é Recruiter — pode tanto gerenciar o sistema quanto operar vagas
- Um Recruiter que gerencia uma vaga específica junto com um Hiring Manager

A verificação sempre usa lógica **OR** — o usuário precisa ter **pelo menos uma** das roles exigidas para a ação.

---

## Seed das roles

As quatro roles são criadas automaticamente via Seeder na instalação do sistema:

🚧 *Seeder pendente de implementação.*

```php
// RoleSeeder — valores que serão inseridos
[
    ['name' => 'Admin',          'slug' => 'admin'],
    ['name' => 'Recruiter',      'slug' => 'recruiter'],
    ['name' => 'Hiring Manager', 'slug' => 'hiring-manager'],
    ['name' => 'Candidate',      'slug' => 'candidate'],
]
```

---

## Respostas de erro

| Situação | HTTP Status | Mensagem |
|---|---|---|
| Usuário não autenticado | `401 Unauthorized` | `Unauthenticated.` |
| Usuário autenticado sem a role necessária | `403 Forbidden` | `Usuário não tem a função necessária para acessar esse recurso.` (mensagem do `CheckRole`) |
| Usuário com role correta mas sem acesso ao recurso específico | `403 Forbidden` | `This action is unauthorized.` |

Para entender como essas roles se aplicam ao fluxo de candidaturas, veja [Pipeline de Vagas](./06-pipeline.md).
