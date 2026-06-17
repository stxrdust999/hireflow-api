# HireFlow | Applicant Tracking System (ATS)

**HireFlow** is a comprehensive recruitment management platform developed as a technical portfolio project to demonstrate a professional, full-stack architecture. Designed to centralize the entire hiring lifecycle—from job opening creation to the final contract—it bridges the gap between candidates and internal teams (HR and Hiring Managers) through a structured, enterprise-grade evaluation pipeline.

---

## 🏗️ Architecture & Philosophy

HireFlow is built as a **decoupled system**, mimicking high-scale corporate environments where the frontend and backend live in separate ecosystems.

- **[hireflow-api](./docs/02-architecture.md#hireflow-api-laravel):** A robust Laravel 11 REST API managing business logic, RBAC, and data persistence.
- **[hireflow-web](./docs/02-architecture.md#hireflow-web-nextjs):** A modern Next.js 14 frontend (🚧 in construction) that consumes the API via strictly typed contracts generated from Swagger.

---

## ✨ Key Features

- **Dynamic Pipeline:** Candidates move through customizable stages (Screening, Technical Interview, Offer) with a full audit log of every movement.
- **Advanced RBAC:** Fine-grained permissions for 4 personas: `Candidate`, `Recruiter`, `Hiring Manager`, and `Admin`.
- **Collaborative Hiring:** Internal comments and evaluations that keep the conversation organized within the candidate's profile.
- **Async Notifications:** Emails and system alerts handled via Redis queues to ensure zero-latency API responses.
- **Social Auth:** Seamless onboarding via LinkedIn OAuth integration.

---

## 🛠️ Tech Stack

### Backend (hireflow-api)

- **Framework:** Laravel 11 / PHP 8.2+
- **Database:** MySQL 8.0 (Persistence)
- **Cache/Queue:** Redis 7 (Speed & Async Jobs)
- **Auth:** Laravel Sanctum (SPA/API) + Socialite (OAuth)
- **Documentation:** L5-Swagger (OpenAPI 3.0)

### Frontend (hireflow-web)

- **Framework:** Next.js 14 (App Router)
- **State/Data:** TanStack Query + Orval (Auto-generated API clients)

---

## 🚀 Quick Start (API)

### Prerequisites

- [Laravel Herd](https://herd.laravel.com/) (Recommended) or PHP 8.2+
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (for MySQL and Redis)
- [Composer](https://getcomposer.org/)

### Installation

1.  **Clone & Install:**

    ```bash
    git clone https://github.com/your-repo/hireflow-api.git
    cd hireflow-api
    composer install
    ```

2.  **Environment Setup:**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    \_Note: Remember to configure your `DB\__`and`REDIS\__`variables in the`.env` file.\_

3.  **Database & Infrastructure:**

    ```bash
    docker compose up -d
    php artisan migrate --seed
    ```

4.  **Serve:**
    If using Laravel Herd, the project is available at `http://hireflow-api.test`. Otherwise, run:
    ```bash
    php artisan serve
    ```

---

## 📖 Detailed Documentation

The project includes exhaustive documentation covering every aspect of the system and can all be found in the **[Wiki](https://github.com/stxrdust999/hireflow-api/wiki)** of the project:

1.  **[Introduction](https://github.com/stxrdust999/hireflow-api/wiki/01-%E2%80%94-Introdu%C3%A7%C3%A3o-%F0%9F%91%8B):** Project vision and problem-solving.
2.  **[Architecture](https://github.com/stxrdust999/hireflow-api/wiki/02-%E2%80%94-Arquitetura-%F0%9F%8F%97%EF%B8%8F):** Deep dive into the dual-system setup.
3.  **[Database Schema](https://github.com/stxrdust999/hireflow-api/wiki/03-%E2%80%94-Banco-de-Dados-%F0%9F%97%84%EF%B8%8F):** Entity-relationship diagrams and UUID strategy.
4.  **[Authentication](github.com/stxrdust999/hireflow-api/wiki/04-—-Autenticação-%F0%9F%94%90):** Sanctum flow and LinkedIn integration.
5.  **[Roles & Permissions](https://github.com/stxrdust999/hireflow-api/wiki/05-%E2%80%94-Roles-&-Permiss%C3%B5es-%F0%9F%9B%A1%EF%B8%8F):** The logic behind the RBAC system.
6.  **[Recruitment Pipeline](https://github.com/stxrdust999/hireflow-api/wiki/06-%E2%80%94-Pipeline-de-Vagas-%F0%9F%94%84):** How the stage logic works.
7.  **[API Conventions](https://github.com/stxrdust999/hireflow-api/wiki/07-%E2%80%94-Conven%C3%A7%C3%B5es-da-API-%F0%9F%93%A1):** REST patterns and response standards.
8.  **[Use Cases](https://github.com/stxrdust999/hireflow-api/wiki/12-%E2%80%94-Casos-de-Uso-%F0%9F%8E%AC):** Step-by-step user journeys.

> ⚠️ Not all topics that were covered in the wiki are listed here.
