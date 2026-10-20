# Sistem Informasi Layanan Pengaduan dan Aspirasi Warga Desa Banjarsari

Aplikasi web full-stack monolit berbasis Laravel untuk pengelolaan pengaduan dan aspirasi warga Desa Banjarsari. Sistem ini memvalidasi pendaftaran warga secara otomatis terhadap data kependudukan desa, mengelola disposisi laporan oleh Admin Desa, serta menyediakan supervisi untuk Kepala Desa.

---

## 1. Prasyarat Sistem

Sebelum menjalankan aplikasi di lingkungan lokal, pastikan perangkat Anda telah terpasang:
- **PHP 8.2+** (disarankan ekstensi: `pdo_mysql`, `pdo_sqlite`, `bcmath`, `mbstring`, `xml`, `curl`)
- **Composer** (v2.x)
- **Node.js** (LTS v20+ atau v22+) & **npm**
- **MySQL 8.x** atau **MariaDB 10.x**

---

## 2. Langkah Instalasi & Menjalankan Aplikasi

Ikuti urutan langkah berikut untuk menyiapkan proyek di lingkungan lokal:

```bash
# 1. Clone repository
git clone <URL-REPOSITORY>
cd Ruang_Banjarsari

# 2. Pasang dependensi backend PHP
composer install

# 3. Pasang dependensi frontend JavaScript
npm install

# 4. Salin file konfigurasi environment
cp .env.example .env

# 5. Buat application key baru
php artisan key:generate

# 6. Buat database MySQL lokal (melalui MySQL CLI / phpMyAdmin)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS pengaduan_banjarsari;"

# 7. Jalankan migrasi dan seeder data awal
php artisan migrate --seed

# 8. Buat symbolic link direktori berkas publik
php artisan storage:link

# 9. Jalankan Vite asset bundler (pada terminal 1)
npm run dev

# 10. Jalankan server lokal Laravel (pada terminal 2)
php artisan serve
```

Aplikasi dapat diakses melalui peramban web di: `http://localhost:8000`

---

## 3. Akun Pengguna Bawaan (Development Seeder)

> ⚠️ **PERINGATAN KEAMANAN:**
> Seluruh akun dan data kependudukan di bawah ini menggunakan data **FIKTIF** untuk keperluan pengembangan lokal. Password default adalah `password`. **WAJIB DIGANTI** apabila aplikasi dideploy di luar lingkungan lokal!

| Peran | Identifier Login | Password | Status Akun | Catatan |
|---|---|---|:---:|---|
| **Admin Desa** | `admin@desa-banjarsari.test` | `password` | `aktif` | Pengelola pengaduan & tinjauan akun |
| **Kepala Desa** | `kades@desa-banjarsari.test` | `password` | `aktif` | Supervisi laporan & statistik |
| **Warga (Contoh 1)** | NIK: `9999000000000001`<br>Email: `warga.aktif@desa-banjarsari.test` | `password` | `aktif` | Budi Santoso (cocok kependudukan) |
| **Warga (Contoh 2)** | NIK: `9999000000000002`<br>Email: `warga.pending@desa-banjarsari.test` | `password` | `menunggu_tinjauan` | Siti Aminah (menunggu tinjauan manual) |

---

## 4. Struktur Folder Proyek

```text
Ruang_Banjarsari/
├── app/
│   ├── Enums/                     # Backed Enum PHP (Role, StatusAkun, StatusLaporan, JenisLaporan)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/             # Controller khusus Admin Desa
│   │   │   ├── Kepala/            # Controller khusus Kepala Desa
│   │   │   └── Warga/             # Controller khusus Warga
│   │   └── Middleware/            # EnsureRole, EnsureAccountActive, SecurityHeaders
│   ├── Models/                    # User, DataKependudukan, Kategori, Pengaduan, Lampiran, dll
│   ├── Policies/                  # PengaduanPolicy, KomentarPolicy
│   └── Services/                  # Lapisan logika bisnis (Service Layer)
├── database/
│   ├── factories/                 # Factory pengujian model
│   ├── migrations/                # Skema tabel MySQL & SQLite
│   └── seeders/                   # Seeder kategori, data kependudukan fiktif, user seeder
├── docs/                          # Dokumentasi arsitektur dan ERD database
│   ├── ARSITEKTUR.md
│   └── DATABASE.md
├── resources/
│   ├── css/ & js/                 # Asset Tailwind & Alpine.js
│   └── views/
│       ├── admin/                 # Blade view Admin Desa
│       ├── components/            # Komponen Blade reusable (flash-alert, modal, dll)
│       ├── errors/                # Halaman error kustom (403 berbahasa Indonesia)
│       ├── kepala/                # Blade view Kepala Desa
│       ├── layouts/               # Layout aplikasi mobile-first (app.blade.php)
│       └── warga/                 # Blade view Warga
├── routes/
│   ├── auth.php                   # Rute autentikasi Breeze (Login, Password Reset, Logout)
│   └── web.php                    # Rute terstruktur per grup peran
└── tests/
    ├── Feature/                   # Pengujian integrasi & rute otorisasi
    └── Unit/                      # Pengujian unit enum & model
```

---

## 5. Perintah Pengujian & Standar Gaya Kode

```bash
# Menjalankan seluruh Automated Testing (PHPUnit dengan SQLite in-memory)
php artisan test

# Memeriksa kepatuhan standar gaya kode PSR-12 tanpa mengubah file (CI mode)
./vendor/bin/pint --test

# Memperbaiki format kode secara otomatis menggunakan Laravel Pint
./vendor/bin/pint

# Membangun bundle aset produksi
npm run build
```

---

## 6. Alur Kerja Git & Standar Kolaborasi

Pengembangan capstone ini dilakukan oleh tim 3 orang menggunakan kerangka kerja Scrum selama 4 minggu.

### Strategi Branch
- **`main`**: Branch produksi yang selalu dalam kondisi stabil dan siap deploy.
- **`develop`**: Branch integrasi utama tempat penggabungan fitur-fitur sprint.
- **`feature/PBI-<nomor>-<nama-singkat>`**: Branch pengerjaan fitur per Product Backlog Item.
  - *Contoh:* `feature/PBI-06-form-registrasi-nik`
  - *Contoh:* `feature/PBI-09-ajukan-pengaduan`

### Konvensi Commit
Setiap pesan commit wajib menyertakan nomor PBI:
```text
PBI #<nomor>: <deskripsi ringkas perubahan>
```
*Contoh:*
- `PBI #1: inisialisasi struktur base code dan layout responsive`
- `PBI #3: tambahkan migration data_kependudukan dan enum status`
- `PBI #5: implementasikan middleware EnsureRole dan rate limiting`

### Pull Request
Setiap pull request ke branch `develop` atau `main` wajib mengisi template `.github/pull_request_template.md` dan memastikan seluruh pengujian pada GitHub Actions CI berstatus hijau.
