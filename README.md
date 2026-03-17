# 🏪 Nata Toko — Modern POS System

**Nata Toko** is a modern, full-featured Point of Sale (POS) system built with Laravel 11, Vue 3, and Inertia.js. It supports real-time updates via WebSockets, QRIS payment integration through Mayar.id, and a clean dual-portal architecture (Admin + Cashier).

LINK DEMO : https://youtu.be/7enzSIXDxH4
LIVE APP : https://natatoko.sgp.dom.my.id

---

## 🛠 Tech Stack

| Layer       | Technology                                |
|-------------|-------------------------------------------|
| Backend     | **Laravel 11** (PHP 8.2+)                 |
| Frontend    | **Vue 3** (Composition API) + Inertia.js  |
| Styling     | **Tailwind CSS** |
| Database    | **MySQL / MariaDB** |
| WebSockets  | **Laravel Reverb** |
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
git clone [https://github.com/your-org/natatoko.git](https://github.com/your-org/natatoko.git)
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
# Use [https://api.mayar.id](https://api.mayar.id) for Production
MAYAR_BASE_URL="[https://api.mayar.id](https://api.mayar.id)"
MAYAR_API_KEY="your_production_mayar_api_key"
```

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
php artisan migrate
```

> **⚠️ IMPORTANT:** Do **NOT** run `php artisan db:seed` or `migrate --seed` at this stage. You **must** create the initial Super Admin account through the web interface first before inserting any demo transactional data.

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

## 💳 Real-World Mayar.id (QRIS) Integration

To use Mayar.id for actual live transactions in your store, you must configure the Webhook. The Webhook allows Mayar's servers to silently notify your application the exact moment a customer successfully pays via their mobile banking/e-wallet, automating the checkout flow without requiring the cashier to refresh the page.

### 1. Obtain Production API Keys
1. Log in to your [Mayar.id Dashboard](https://app.mayar.id).
2. Ensure you are in **Live/Production Mode**.
3. Navigate to **Settings -> Developer / API Keys**.
4. Generate and copy your Live API Key into your `.env` file as `MAYAR_API_KEY`.

### 2. Configure Webhooks (Crucial for Automation)
Your server must be accessible from the internet (e.g., deployed on a VPS or using a tunneling service like Ngrok for local development).
1. In the Mayar Dashboard, navigate to **Settings -> Webhooks**.
2. Click **Add Webhook Endpoint**.
3. Set the Endpoint URL to your application's designated webhook route (e.g., `https://your-domain.com/api/webhooks/mayar`).
4. Select the event triggers (typically `payment.success` or `transaction.paid`).
5. Save the configuration. 

### 3. POS Live Payment Flow
1. **Initiate Checkout:** The cashier selects the items and chooses **QRIS (Mayar.id)** as the payment method.
2. **Generate QRIS:** Nata Toko communicates with the Mayar API and instantly displays a dynamic QRIS code on the screen.
3. **Customer Scans:** The customer scans the QR code with their banking app or e-wallet (GoPay, OVO, Dana, etc.) and completes the payment.
4. **Instant Update:** Mayar fires the Webhook to your server. 
5. **WebSocket Push:** Nata Toko processes the Webhook and uses Laravel Reverb to push an event to the frontend. The POS screen instantly updates to **"Payment Successful"** and prints the receipt.

---

## 🔐 First Time Setup (Required)

When you visit the application for the first time (`http://127.0.0.1:8000/`), you will be automatically redirected to `/setup`.

On this page, you'll create the **Super Admin** account by providing:

- **Name** — Admin display name
- **Email** — Used for Admin panel login
- **Password** — Used for Admin panel login
- **PIN** — 6-digit PIN used for POS (cashier) login and action authorization

After setup, the `/setup` route is permanently locked and your database will have its first active user.

### Login Portals

| Portal      | URL            | Authentication     |
|-------------|----------------|-------------------|
| Admin Panel | `/login`       | Email + Password  |
| POS Cashier | `/pos/login`   | 6-digit PIN       |

---

## 🌱 Populating Demo Data (Optional)

After successfully creating the Super Admin account in the step above, you can now safely populate the application with demo data. Running these seeders requires an existing user in the database to link the transactions.

Open your terminal and run the specific seeders in this order:

**1. Insert Master Data (Partners & Box Templates)**
```bash
php artisan db:seed --class=PartnerSeeder
php artisan db:seed --class=BoxTemplateSeeder
```

**2. Insert Transactional & Trend Data**
```bash
# Generates past shop sessions, historical orders, and 30-day trends
php artisan db:seed --class=TrendSeeder

# Generates specific upcoming/pending orders for today's dashboard simulation
php artisan db:seed --class=TodayBoxOrderSeeder
```

---

## 📁 Project Structure

```text
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
```
