# 🖥️ E-Commerce PC Builder Frontend (Storefront + Admin Dashboard)

## 1) Project Overview 🛒

This repository contains the **React (Vite)** frontend for an **E-Commerce PC Builder** platform, covering both:

- 🛍️ **Storefront**: Product browsing and a guided custom PC build experience
- 🧑‍💼 **Admin Dashboard**: Catalog management and operational workflows

---

## 2) Key Features ✨

### 🧩 Custom PC Builder (PhongVu-style Flow)

- Step-by-step component selection (CPU, motherboard, RAM, GPU, PSU, case, etc.)
- **Real-time compatibility filtering** to prevent invalid builds (e.g., socket matching)
- Clear UX feedback as parts are selected/removed

### 🛠️ Comprehensive Admin Panel

- CRUD workflows for managing categories, products, attributes, and inventory-like data
- Rich table views with sorting, paging, and actions for large datasets

---

## 3) Tech Stack 🧰

- ⚛️ **React**
- ⚡ **Vite**
- 🎨 **Material UI (MUI)**
- 📊 **@mui/x-data-grid** (complex admin tables)
- 🧾 **react-hook-form** (form state + validation)
- 🔔 **notistack** (snackbars/notifications)
- 🌐 **axios** (API calls)

---

## 4) Setup Instructions 🚀

### Prerequisites

- Node.js (LTS recommended)

### Step-by-step

1) Go to the frontend folder:

```bash
cd frontend-ecommerce
```

2) Install dependencies:

```bash
npm install
```

3) Configure environment variables:

Create a `.env` file in `frontend-ecommerce/` and set your API base URL:

```bash
VITE_API_URL=http://127.0.0.1:8000
```

> Vite only exposes env variables prefixed with `VITE_` to the client.

4) Start the dev server:

```bash
npm run dev
```

Vite will print the local URL (typically `http://localhost:5173`).

---

## 5) Default Test Accounts 🔑

Use the following account to access the Admin Dashboard immediately:

- **Admin**: `admin@test.com`
- **Password**: `password`
