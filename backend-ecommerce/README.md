# 🖥️ E-Commerce PC Builder Backend (Laravel • Porto Architecture)

## 1) Project Overview 🧩

This repository contains the backend API for an **E-Commerce PC Builder** platform. The system is designed to power a configurable product experience (CPU, motherboard, RAM, GPU, PSU, case, etc.) with **dynamic compatibility rules** (e.g., socket matching) and a scalable, modular backend structure.

The codebase is organized using the **Porto Architecture** (Container-based modularization) to keep business logic isolated, testable, and easy to extend as the domain grows.

---

## 2) Key Features & Architecture 🏗️

### 🧱 Porto Architecture (Containers)

- **Modular containers**: Domain logic is grouped by feature/domain under `app/Containers/...`.
- **Separation of concerns**: Actions/Tasks/Models/Controllers are composed to keep controllers thin and business rules centralized.
- **Scalable boundaries**: Each container can evolve independently while still integrating via shared services/contracts.

### 🧬 EAV (Entity–Attribute–Value) for Dynamic Compatibility

- Uses an **EAV-style data model** to represent product attributes (e.g., socket, chipset, form-factor) without hard-coding schema changes for every attribute.
- Enables **dynamic compatibility checks** such as:
    - CPU socket ↔ motherboard socket
    - RAM type/speed ↔ motherboard supported memory
    - Case form-factor ↔ motherboard form-factor
    - PSU wattage ↔ full build power budget

### 🧰 Dependency Injection (DI)

- Leverages Laravel’s **IoC container** to keep services loosely coupled.
- Improves unit-testability and reduces direct dependencies between layers.

### 🔐 Authentication (Laravel Passport)

- **OAuth2 token-based authentication** powered by Laravel Passport.
- Suitable for SPA/mobile clients and service-to-service access.

### 🛡️ RBAC (Spatie Permission)

- **Roles & Permissions** via `spatie/laravel-permission`.
- Supports fine-grained authorization for admin workflows (catalog management, user management, order operations, etc.).

---

## 3) Tech Stack 🧰

- **PHP**: 8+
- **Framework**: Laravel
- **Database**: MySQL / PostgreSQL
    - Current environment uses **Serverless Neon (PostgreSQL)**, so a local DB setup is **not strictly required** for development.
- **Auth**: Laravel Passport (OAuth2)
- **Authorization**: Spatie Permission (RBAC)

---

## 4) Setup Instructions (Local) 🚀

> Notes
>
> - This backend is configured to work with a **cloud-hosted Neon database**.
> - **Migrations/seeders are optional** for local setup if you are pointing at a prepared cloud database.
> - Make sure your PHP installation includes the required DB driver (e.g., `pdo_pgsql` for PostgreSQL).

### Step-by-step

1. Go to the backend folder:

```bash
cd backend-ecommerce
```

2. Install PHP dependencies:

```bash
composer install
```

3. Create and configure environment variables:

```bash
copy .env.example .env
php artisan key:generate
```

Update `.env` as needed (typical keys):

- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`
- Database connection (Neon example): `DB_URL=postgresql://...` or `DATABASE_URL=...` (depending on your configuration)

4. (Optional) Run migrations & seeders:

> Only do this if you are using a local database or you intentionally want to create/reset schema in your target DB.

```bash
php artisan migrate
php artisan db:seed
```

5. Run the API locally:

```bash
php artisan serve
```

The API will typically be available at `http://127.0.0.1:8000`.

---

## 5) API Documentation 📮

- A **Postman Collection** is intended to be included in the **repository root directory** for quick API evaluation.
    - Look for a file matching `*.postman_collection.json`.
    - If it’s not present in your checkout, request it from the project maintainer or team lead.
