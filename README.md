# 🧩 E-Commerce PC Builder — Monorepo (Laravel + React)

🎥 **Video Demo (watch this first):** [Link to Video Demo]

> The video walkthrough highlights the **EAV-driven compatibility logic** and the **Admin features** end-to-end.

---

## 1) Project Overview 🧠

This repository is a **monorepo** containing:

- 🛠️ **Laravel backend API** (Porto Architecture) — `backend-ecommerce/`
- 🛍️ **React storefront + Admin Dashboard** (Vite) — `frontend-ecommerce/`

### Core business logic

The application focuses on a **Custom PC Builder** experience with **real-time compatibility checking**, ensuring users can only create valid builds.

Typical rules include (not limited to):

- CPU socket ↔ motherboard socket compatibility
- RAM type/speed ↔ motherboard supported memory
- Case form-factor ↔ motherboard form-factor
- PSU capacity ↔ estimated build power draw

---

## 2) Video Demo 🎬

**[Link to Video Demo]**

---

## 3) Backend Architecture & Tech Stack 🏗️

Backend location: `backend-ecommerce/`

### 🧱 Porto Architecture (Actions • Tasks • Repositories)

The backend follows the **Porto Architecture** to enforce strong separation of concerns and scalable modular boundaries:

- **Actions** orchestrate use-cases (application-level workflows)
- **Tasks** encapsulate reusable domain operations
- **Repositories** isolate persistence concerns and data access patterns

### 🧬 EAV (Entity–Attribute–Value) Database Model

To support rapidly changing hardware specs and compatibility requirements, the backend uses an **EAV model** for flexible attribute definition and querying.

This enables dynamic component compatibility such as **CPU/Mainboard socket matching** without constantly redesigning tables.

### 🧰 Dependency Injection (DI)

DI is applied throughout Actions/Tasks via Laravel’s IoC container to keep code **testable**, **composable**, and **loosely coupled**.

### 🔐 Authentication & RBAC

- **Authentication**: Laravel Passport (OAuth2 / token-based auth)
- **Authorization**: Spatie Permission (RBAC via roles & permissions)

### 🗄️ Database (Serverless Neon)

- **PostgreSQL** hosted on **Serverless Neon**
- Designed so that **no local DB setup or seeding is required** for reviewers (when environment variables point to the shared cloud database)

---

## 4) Frontend Tech Stack 🎨

Frontend location: `frontend-ecommerce/`

- ⚛️ **React**
- ⚡ **Vite**
- 🎨 **Material UI (MUI)**
- 📊 **@mui/x-data-grid** (complex admin tables)
- 🧾 **react-hook-form** (forms + validation)
- 🔔 **notistack** (notifications)
- 🌐 **axios** (API calls)

---

## 5) Local Setup Instructions 🚀

### Backend (Laravel) 🛠️

```bash
cd backend-ecommerce
composer install
copy .env.example .env
php artisan key:generate
php artisan serve
```

Notes:

- Configure DB env vars in `backend-ecommerce/.env` to point to Neon (PostgreSQL).
- Migrations/seeders are intentionally **not required** for reviewers if the cloud database is already provisioned.

### Frontend (React + Vite) 🛍️

```bash
cd frontend-ecommerce
npm install
```

Create `frontend-ecommerce/.env`:

```bash
VITE_API_URL=http://127.0.0.1:8000
```

Run the dev server:

```bash
npm run dev
```

---

## 6) Testing & API 🧪📮

### Postman Collection

- Import the Postman collection from the repo root:
  - `Ecommerce-PC-Builder.postman_collection.json`

### Default Test Accounts

- **Admin**: `admin@test.com` / `password`
- **Customer**: `customer@test.com` / `password`
