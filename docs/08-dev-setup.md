# 08 — Setup de Desenvolvimento

## Pré-requisitos

Antes de começar, certifique-se de ter as seguintes ferramentas instaladas:

| Ferramenta | Versão mínima | Finalidade |
|---|---|---|
| [Laravel Herd](https://herd.laravel.com/) | Latest | Servidor PHP + Nginx local |
| [Docker Desktop](https://www.docker.com/products/docker-desktop/) | Latest | Containers MySQL e Redis |
| [Node.js](https://nodejs.org/) | 18+ | Runtime do frontend Next.js |
| [Composer](https://getcomposer.org/) | 2+ | Gerenciador de dependências PHP |
| [PHP](https://www.php.net/) | 8.2+ | Runtime da API Laravel (fornecido pelo Herd) |

---

## Estrutura esperada de pastas

```
www/
├── hireflow-api/        ← projeto Laravel
└── hireflow-web/        ← projeto Next.js
```

O Herd deve apontar para a pasta `www/` para servir ambos os projetos automaticamente.

---

## 1. Clonar os repositórios

```bash
# Dentro da pasta www/
git clone https://github.com/seu-usuario/hireflow-api
git clone https://github.com/seu-usuario/hireflow-web
```

---

## 2. Configurar a API (hireflow-api)

### 2.1 — Instalar dependências PHP

```bash
cd hireflow-api
composer install
```

### 2.2 — Configurar variáveis de ambiente

Copie o arquivo de exemplo e preencha com suas credenciais:

```bash
cp .env.example .env
php artisan key:generate
```

Abra o `.env` e configure as seguintes variáveis:

```env
APP_NAME=HireFlow
APP_URL=http://hireflow-api.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hireflow
DB_USERNAME=hireflow_user
DB_PASSWORD=sua_senha_aqui

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=sua_senha_redis_aqui
REDIS_PORT=6379

SANCTUM_STATEFUL_DOMAINS=localhost:3000,hireflow-web.test

LINKEDIN_CLIENT_ID=seu_client_id
LINKEDIN_CLIENT_SECRET=seu_client_secret
LINKEDIN_REDIRECT_URI=http://hireflow-api.test/api/v1/auth/linkedin/callback
```

> **Senhas:** gere senhas seguras com `openssl rand -base64 16` para `DB_PASSWORD` e `REDIS_PASSWORD`.

> **LinkedIn OAuth:** as credenciais `LINKEDIN_CLIENT_ID` e `LINKEDIN_CLIENT_SECRET` são obtidas criando um app no [LinkedIn Developers](https://www.linkedin.com/developers/). 🚧 *Configuração detalhada pendente.*

### 2.3 — Subir os containers Docker

O `docker-compose.yml` está dentro de `hireflow-api/`:

```bash
docker compose up -d
```

Verifique se os containers estão rodando:

```bash
docker compose ps
```

Saída esperada:

```
NAME             IMAGE            STATUS
hireflow_mysql   mysql:8.0        Up
hireflow_redis   redis:7-alpine   Up
```

### 2.4 — Rodar as migrations e seeders

```bash
php artisan migrate --seed
```

Isso criará todas as tabelas e populará o banco com dados iniciais (roles, empresa de exemplo, usuários de teste).

### 2.5 — Verificar o Herd

O Herd deve detectar automaticamente o projeto e servi-lo em:

```
http://hireflow-api.test
```

Se não aparecer, abra o Herd, vá em **Sites** e adicione manualmente apontando para `www/hireflow-api/public`.

Teste a API:

```bash
curl http://hireflow-api.test/api/v1/job-openings
```

Resposta esperada: `{"data": [], "meta": {...}}` (lista vazia ou com dados do seed).

---

## 3. Configurar o Frontend (hireflow-web)

🚧 *Projeto não iniciado — instruções pendentes.*

```bash
cd hireflow-web
npm install
cp .env.local.example .env.local
npm run dev
```

O frontend estará disponível em `http://localhost:3000`.

---

## 4. Usuários de teste (após seed)

🚧 *Seed pendente de implementação — usuários abaixo serão criados automaticamente.*

| Role | Email | Senha |
|---|---|---|
| Admin | `admin@hireflow.test` | `password` |
| Recruiter | `recruiter@hireflow.test` | `password` |
| Hiring Manager | `manager@hireflow.test` | `password` |
| Candidate | `candidate@hireflow.test` | `password` |

---

## 5. Comandos úteis do dia a dia

### API (Laravel)

```bash
# Rodar migrations do zero (apaga tudo e recria)
php artisan migrate:fresh --seed

# Rodar apenas os seeders novamente
php artisan db:seed

# Limpar todos os caches
php artisan optimize:clear

# Iniciar worker de filas (Redis)
php artisan queue:work redis

# Gerar documentação Swagger
php artisan l5-swagger:generate

# Ver todas as rotas registradas
php artisan route:list
```

### Docker

```bash
# Subir containers em background
docker compose up -d

# Parar containers
docker compose down

# Ver logs em tempo real
docker compose logs -f

# Acessar o MySQL via linha de comando
docker exec -it hireflow_mysql mysql -u hireflow_user -p hireflow
```

---

## Problemas comuns

### "Connection refused" ao conectar no MySQL
Verifique se o container está rodando com `docker compose ps`. Se não estiver, rode `docker compose up -d`.

### Migrations falham com erro de foreign key
Sempre use `migrate:fresh` em desenvolvimento — ele dropa e recria tudo na ordem correta.

### Herd não serve o projeto
Certifique-se de que o Herd aponta para a pasta `www/` e que o projeto está em `www/hireflow-api/`. O Herd usa o nome da pasta como domínio (`.test`).

### Erro 419 nas requisições
O token CSRF não está sendo enviado. Em APIs puras com Sanctum, certifique-se de que as rotas estão em `routes/api.php` e não em `routes/web.php`.
