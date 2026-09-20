<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Tax Revenue & Performance Monitoring System

Language / Bahasa:
- [Bahasa Indonesia](#bahasa-indonesia)
- [English](#english)
- [About Laravel Framework](#about-laravel)

---

## Bahasa Indonesia

### 📌 Latar Belakang & Deskripsi Proyek

Aplikasi ini dikembangkan untuk mengatasi permasalahan dan keluhan pegawai terkait ketidakjelasan, ketidaktransparanan, dan hilangnya data penelusuran (*tracking*) penerimaan atas kinerja mereka. Ketidakmampuan untuk melacak detail penerimaan secara transparan sering kali memicu kekecewaan karena penilaian kinerja tidak mencerminkan kondisi sebenarnya di lapangan.

Sistem ini hadir **bukan untuk menggantikan aplikasi yang sudah ada**, melainkan sebagai **solusi pelengkap** untuk menjawab seluruh permasalahan terkait penerimaan yang sebelumnya tidak dapat dijelaskan rincian dan asal-usulnya.

Aplikasi ini menyajikan **Dashboard Monitoring** interaktif beserta rincian kinerja berdasarkan:
* Jenis Penerimaan
* Fungsi Pengawasan
* Fungsi Pemeriksaan
* Fungsi Penagihan
* Penjagaan Harian (*Daily Guarding / Monitoring*)

Sistem ini dirancang khusus untuk mempermudah pimpinan dalam melakukan pemantauan secara *real-time* dan mengambil keputusan strategis secara presisi berbasis data.

### ✨ Fitur Utama

* **Dashboard Monitoring Interaktif**: Visualisasi data penerimaan dan kinerja pegawai secara komprehensif.
* **Tracking Detail Kinerja**: Penelusuran transparan per jenis penerimaan serta fungsi pengawasan, pemeriksaan, dan penagihan.
* **Penjagaan Harian (Daily Guarding)**: Pemantauan harian untuk transparansi penuh penilaian kinerja.
* **High Performance Query & Data Optimization**: Optimasi performa database untuk menangani dan memuat himpunan data (*large dataset*) berukuran besar secara cepat dan efisien.

### 🛠️ Teknologi & Tools

* **Backend**: PHP (Laravel Framework)
* **Frontend**: Tailwind CSS & Alpine.js (TALL Stack approach)
* **Database**: MySQL (XAMPP Environment)
* **Performance Tuning**: Optimasi kueri database (*indexing*, *eager loading*, *query optimization*) untuk pemrosesan data bervolume tinggi.

---

## English

### 📌 Background & Project Overview

This application was developed to address employee concerns regarding the lack of transparency, ambiguity, and untraceability of performance-related revenues. The inability to track detailed revenue data often leads to dissatisfaction when performance evaluations do not align with actual conditions in the field.

Rather than replacing existing systems, this application serves as a **complementary solution** designed to fill the gaps and resolve issues related to revenue data whose origin and details were previously untraceable.

The platform provides an interactive **Monitoring Dashboard** featuring detailed performance tracking by:
* Revenue Type
* Supervision/Monitoring Function
* Audit/Tax Examination Function
* Tax Collection Function
* Daily Guarding & Tracking (*Daily Monitoring*)

It enables leadership to conduct real-time monitoring and make precise, data-driven strategic decisions.

### ✨ Key Features

* **Interactive Monitoring Dashboard**: Comprehensive visualization of revenue and staff performance data.
* **Detailed Performance Tracking**: Transparent breakdown by revenue category as well as supervision, examination, and collection functions.
* **Daily Monitoring & Guarding**: Daily operational tracking ensuring full accountability and fair performance evaluation.
* **High Performance Query & Data Optimization**: Optimized database architecture designed to handle and load large datasets rapidly and efficiently.

### 🛠️ Tech Stack & Tools

* **Backend**: PHP (Laravel Framework)
* **Frontend**: Tailwind CSS & Alpine.js
* **Database**: MySQL (XAMPP Environment)
* **Performance Tuning**: Specialized database query optimizations (*indexing*, *eager loading*, and lightweight data retrieval) to ensure high-speed loading for heavy datasets.

---

## 🚀 Cara Penggunaan / How to Run

1. **Clone repository:**
   ```bash
   git clone https://github.com/username/repository-name.git
   cd repository-name
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Environment Setup:**
   Salin berkas `.env.example` menjadi `.env` dan sesuaikan konfigurasi database MySQL Anda (XAMPP).
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Migration & Run Server:**
   ```bash
   php artisan migrate
   php artisan serve
   ```

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).