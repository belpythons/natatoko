# Posita POS System

Sistem Point of Sale (POS) modern berbasis web untuk manajemen kantin dan penjualan box makanan.

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12 |
| Frontend | Vue 3 (Composition API), Inertia.js |
| Styling | Tailwind CSS, Shadcn UI |
| Database | MySQL |
| Auth | Laravel Breeze |

---

## 🚀 Instalasi

### Prerequisites
- PHP 8.4+
- Composer
- Node.js 20+
- MySQL

### Setup Steps

```bash
# 1. Clone repository
git clone <repo-url> posita
cd posita

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database di .env
# DB_CONNECTION=mysql
# # DB_HOST=127.0.0.1
# # DB_PORT=3306
# # DB_DATABASE=retailer
# # DB_USERNAME=root
# # DB_PASSWORD=

# 5. Migrate & Seed
php artisan migrate:fresh --seed

# 6. Run development server
npm run dev
php artisan serve

# 7. Initial Access
# Akses aplikasi di http://localhost:8000.
# Jika database masih kosong (belum ada admin), Anda akan otomatis diarahkan ke halaman `/setup` untuk membuat akun Admin pertama.
```

### Test Credentials

| Role | Email | Password | PIN |
|------|-------|----------|-----|
| Admin | admin@posita.com | password | 123456 |
| Staff | staff@posita.com | password | 654321 |

---

## 📁 Pembagian Fitur & PIC

### Order Box — **Rivaldi (202312050)**
Fitur pemesanan box makanan (snack box, heavy meal).

| Tipe | Path |
|------|------|
| Controller | `app/Http/Controllers/Pos/BoxOrderController.php` |
| Service | `app/Services/BoxOrderService.php` |
| Model | `app/Models/BoxOrder.php`, `app/Models/BoxOrderItem.php` |
| Pages | `resources/js/Pages/Pos/Box/Index.vue`, `Create.vue` |

---

### Open Shop — **Nurita Wahyuni (202312061)**
Fitur membuka sesi toko harian dengan input modal awal.

| Tipe | Path |
|------|------|
| Controller | `app/Http/Controllers/Pos/ShopSessionController.php` |
| Page | `resources/js/Pages/Pos/OpenShop.vue` |

---

### Close Shop — **Muhammad Ammar Alfarabi (202312056)**
Fitur menutup sesi toko, rekap penjualan, verifikasi uang fisik.

| Tipe | Path |
|------|------|
| Page | `resources/js/Pages/Pos/CloseShop.vue` |
| Service | `app/Services/ReportService.php` |

---

### Core & Admin — **Belva Pranama Sriwibowo (202312066)**
Semua fitur core: Authentication, User Management, Layouts, Konfigurasi.

| Tipe | Path |
|------|------|
| Layouts | `AdminLayout.vue`, `EmployeeLayout.vue` |
| Controllers | `DashboardController.php`, `PartnerController.php`, `BoxTemplateController.php`, `UserManagementController.php` |
| Config | `routes/web.php` |
| UI Components | `resources/js/Components/ui/*` |

---

## 📜 License

unlicense
