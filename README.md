# 🏪 Nata Toko — Modern POS System

**Nata Toko** is a modern, full-featured Point of Sale (POS) system built with Laravel 11, Vue 3, and Inertia.js. It supports real-time updates via WebSockets, QRIS payment integration through Mayar.id, and a clean dual-portal architecture (Admin + Cashier).

---

## 🛠 Tech Stack

| Layer       | Technology                                |
|-------------|-------------------------------------------|
| Backend     | **Laravel 11** (PHP 8.2+)                 |
| Frontend    | **Vue 3** (Composition API) + Inertia.js  |
| Styling     | **Tailwind CSS**                          |
| Database    | **MySQL / MariaDB**                       |
| WebSockets  | **Laravel Reverb**                        |
| Payment     | **Mayar.id** (QRIS)                       |
| Auth        | Laravel Breeze (Admin) + PIN-based (POS)  |

---

## 📋 Prerequisites

Ensure the following are installed on your system:

- **PHP** >= 8.2 (with `mysqli`, `mbstring`, `openssl`, `pdo_mysql` extensions)
- **Composer** >= 2.x
- **Node.js** >= 18.x & **npm** >= 9.x
- **MySQL** >= 8.0 or **MariaDB** >= 10.6

---

## 🚀 Step-by-Step Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/natatoko.git
cd natatoko
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

Copy the example environment file and generate the app key:

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure the following sections:

#### Database

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=natatoko
DB_USERNAME=root
DB_PASSWORD=your_database_password
```

#### Mail (Gmail SMTP with App Password)

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_gmail_app_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="your_email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> **Note:** Use a [Google App Password](https://support.google.com/accounts/answer/185833), not your regular Gmail password.

#### Mayar.id Payment Gateway

```dotenv
MAYAR_BASE_URL="https://api.mayar.id"
MAYAR_API_KEY="your_mayar_api_key"
```

> Get your API key from [Mayar.id Dashboard](https://app.mayar.id) → Settings → API Keys.

#### Laravel Reverb (WebSockets)

```dotenv
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 4. Database Setup

```bash
php artisan migrate --seed
```

This will create all tables and seed essential data (Partners, Box Templates).

### 5. Storage Link

```bash
php artisan storage:link
```

---

## ▶️ Running the Application

Open **three** terminal windows and run the following:

**Terminal 1 — Backend Server:**

```bash
php artisan serve
```

**Terminal 2 — Frontend Dev Server:**

```bash
npm run dev
```

**Terminal 3 — WebSocket Server (for real-time features):**

```bash
php artisan reverb:start
```

The app will be available at: **http://127.0.0.1:8000**

---

## 🔐 First Time Setup

When you visit the application for the first time (`/`), you will be automatically redirected to `/setup`.

On this page, you'll create the **Super Admin** account by providing:

- **Name** — Admin display name
- **Email** — Used for Admin panel login
- **Password** — Used for Admin panel login
- **PIN** — 6-digit PIN used for POS (cashier) login and action authorization

After setup, the `/setup` route is permanently locked.

### Login Portals

| Portal      | URL            | Authentication     |
|-------------|----------------|-------------------|
| Admin Panel | `/login`       | Email + Password  |
| POS Cashier | `/pos/login`   | 6-digit PIN       |

---

## 📁 Project Structure

```
natatoko/
├── app/
│   ├── Http/Controllers/     # Admin & POS controllers
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic services
│   └── Http/Middleware/      # Auth, setup, PIN validation
├── database/
│   ├── migrations/           # Schema definitions
│   └── seeders/              # Initial data (Partners, Templates)
├── resources/js/
│   ├── Components/           # Reusable Vue components
│   ├── Layouts/              # Admin & Employee layouts
│   └── Pages/                # Inertia page components
├── routes/
│   └── web.php               # All route definitions
└── .env                      # Environment configuration
```

---

## 📝 License

This project is proprietary. All rights reserved.
