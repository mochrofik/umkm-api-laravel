# UMKM Stores API - Sistem Marketplace UMKM Berbasis Laravel

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**UMKM Stores** adalah platform API marketplace yang dirancang khusus untuk mendukung digitalisasi UMKM (Usaha Mikro, Kecil, dan Menengah). Sistem ini memfasilitasi transaksi antara pemilik toko UMKM dan pelanggan dengan berbagai fitur modern seperti pencarian toko terdekat, manajemen produk, sistem keranjang belanja, dan pelacakan pesanan.

---

## 🚀 Fitur Utama

### 🔐 Autentikasi & Otorisasi
- **Multi-Role System**: Mendukung peran **Admin**, **Store (Toko)**, dan **Customer (Pelanggan)** menggunakan `spatie/laravel-permission`.
- **Secure Authentication**: Menggunakan **Laravel Sanctum** untuk pengamanan API berbasis token.
- **Profil Pengguna**: Pengaturan profil dan pembaruan data pengguna secara mandiri.

### 🏪 Untuk Pemilik Toko (Store)
- **Manajemen Produk**: CRUD produk dengan kategori menu yang fleksibel.
- **Manajemen Pesanan**: Menerima pesanan masuk dan melakukan pembaruan status pesanan (Pending, Process, Completed, dll).
- **Pengaturan Toko**: Kelola informasi toko, kategori, dan menu.

### 🛒 Untuk Pelanggan (Customer)
- **Nearby Stores**: Mencari toko UMKM terdekat berdasarkan lokasi (Geolocation).
- **Search & Filter**: Mencari toko berdasarkan nama, kategori, atau produk.
- **Sistem Keranjang**: Kelola item belanja (tambah, perbarui jumlah, hapus item).
- **Checkout & Riwayat**: Proses pemesanan produk dan melihat riwayat transaksi.

### 🛡️ Untuk Admin
- **Verifikasi Toko**: Manajemen data toko yang terdaftar di platform.
- **Manajemen Kategori**: Kelola kategori utama aplikasi.
- **Manajemen Pengguna**: Monitoring data pelanggan dan pemilik toko.

---

## 🛠️ Teknologi yang Digunakan

- **Framework**: [Laravel 12](https://laravel.com)
- **Database**: MySQL / MariaDB
- **Auth**: Laravel Sanctum
- **Permission**: Spatie Laravel Permission
- **Utilities**: Laravel Tinker, Laravel Pint

---

## 📦 Cara Instalasi

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek di lingkungan lokal Anda:

### 1. Persyaratan Sistem
- PHP >= 8.2
- Composer
- MySQL >= 5.7 atau MariaDB >= 10.2 (untuk dukungan Full-Text Search)
- Node.js & NPM (Opsional untuk asset bundling)

### 2. Kloning Repositori
```bash
git clone https://github.com/mochrofik/umkm-api-laravel.git
cd umkm-api-laravel
```

### 3. Instalasi Dependensi
```bash
composer install
npm install
```

### 4. Konfigurasi Environment
Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasi database Anda:
```bash
cp .env.example .env
```
Sesuaikan bagian berikut di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Migrasi & Seeding
Jalankan migrasi untuk membuat tabel dan seeder untuk data awal (termasuk role dan akun admin):
```bash
php artisan migrate --seed
```

### 7. Jalankan Server
```bash
php artisan serve
```
Aplikasi akan berjalan di `http://127.0.0.1:8000`.

---

## 🔑 Akun Default (Seeder)

Setelah menjalankan `php artisan migrate --seed`, Anda dapat masuk dengan akun admin berikut:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@gmail.com` | `admin12345` |

---

## 📑 Struktur Endpoint API (Ringkasan)

### Public / Guest
- `POST /api/login` - Masuk ke sistem.
- `POST /api/register` - Daftar akun baru.
- `GET /api/categories-user` - Ambil daftar kategori.
- `GET /api/get-nearby` - Cari toko terdekat.

### Customer (Pelanggan)
- `GET /api/cart` - Lihat isi keranjang.
- `POST /api/cart/add` - Tambah produk ke keranjang.
- `POST /api/order-customer/checkout` - Proses pesanan.
- `GET /api/order-customer/history` - Riwayat pesanan.

### Store (Pemilik Toko)
- `GET /api/product/get-product` - Daftar produk toko.
- `POST /api/product/add-edit` - Tambah/Edit produk.
- `GET /api/order/incoming` - Pesanan masuk.
- `POST /api/order/update-status/{id}` - Update status pesanan.

---

## 📝 Lisensi

Proyek ini bersifat open-source di bawah lisensi [MIT](LICENSE).

---

**Dibuat dengan ❤️ untuk UMKM Indonesia.**
