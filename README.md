# SISMAT — RS UMMI

SISMAT adalah aplikasi manajemen aset rumah sakit. Aplikasi ini digunakan untuk mencatat aset, lokasi penempatan, pemeliharaan, perpindahan, dokumen pendukung, dan pengajuan penghapusan aset.

Data awal berasal dari seeder untuk keperluan demonstrasi. Data yang ditambahkan atau diubah melalui aplikasi disimpan ke database.

## Teknologi

- PHP 8.2 atau lebih baru
- Laravel 12
- Livewire 4
- Blade dan Tailwind CSS 4
- Vite 7
- SQLite untuk instalasi lokal
- Template Admina

Livewire menangani interaksi seperti pencarian, filter, pagination, dan modal tanpa memuat ulang seluruh halaman.

## Persiapan

Siapkan Composer 2, Node.js, dan npm. Untuk Vite pada proyek ini, gunakan Node.js 20.19+ atau 22.12+; disarankan Node.js 22 LTS yang memenuhi batas versi tersebut.

PHP perlu memiliki ekstensi yang dibutuhkan Laravel, termasuk PDO SQLite, SQLite3, Mbstring, OpenSSL, XML, DOM, cURL, dan Fileinfo. Ekstensi GD digunakan bila lingkungan pengolahan gambar memerlukannya.

Periksa versi perangkat yang terpasang:

```bash
php -v
composer --version
node -v
npm -v
```

## Instalasi lokal

Jalankan perintah berikut dari folder proyek yang sudah diunduh atau di-clone.

### 1. Pasang dependency

```bash
composer install
npm ci
```

Composer memasang dependency PHP dari `composer.lock`. npm memasang dependency frontend dari `package-lock.json`.

### 2. Buat konfigurasi aplikasi

Untuk instalasi baru, salin file konfigurasi contoh:

```bash
cp .env.example .env
php artisan key:generate
```

Jika `.env` sudah ada, gunakan file tersebut dan sesuaikan nilainya. Jangan menimpa konfigurasi instalasi yang sudah digunakan.

Atur bagian berikut di `.env`:

```dotenv
APP_NAME="SISMAT RS UMMI"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=en

DB_CONNECTION=sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

Untuk SQLite, biarkan `DB_DATABASE` tidak diisi sebagai variabel aktif: hapus atau komentari baris tersebut. Laravel akan memakai `database/database.sqlite`. Hapus juga `DB_URL` jika sebelumnya berisi koneksi database lain.

`APP_KEY` diisi oleh perintah `key:generate`. Session dan cache menggunakan file agar instalasi lokal lebih sederhana. Konfigurasi ini ditujukan untuk pengembangan lokal.

### 3. Siapkan database dan data awal

Buat file SQLite jika belum ada:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan config:clear
php artisan migrate --seed
```

Migration membuat tabel dan relasi. Seeder mengisi role, pengguna, kategori, gedung, ruangan, supplier, aset, serta contoh data operasional. Pemanggilan seeder per tabel diatur melalui `database/seeders/DatabaseSeeder.php`.

Urutan migration saat ini sudah diperiksa menggunakan SQLite. Jika ingin memakai MySQL, periksa urutan foreign key terlebih dahulu: migration aset saat ini dijalankan sebelum migration supplier yang dirujuknya. Panduan instalasi ini menggunakan SQLite.

### 4. Jalankan aplikasi

Buka dua terminal di folder proyek.

Terminal pertama:

```bash
php artisan serve
```

Terminal kedua:

```bash
npm run dev
```

Buka alamat yang ditampilkan oleh `php artisan serve`, lalu masuk menggunakan salah satu akun di bawah. Biarkan kedua terminal berjalan selama pengembangan.

Jika tidak ingin menjalankan server Vite terus-menerus, buat hasil build terlebih dahulu:

```bash
npm run build
php artisan serve
```

Jika sebelumnya server Vite dihentikan secara paksa dan aplikasi masih mencoba mengaksesnya, hapus file `public/hot` yang tertinggal, lalu muat ulang halaman. Jangan hapus file tersebut saat `npm run dev` sedang digunakan.

Folder `public/vendor/admina` dan berkas `public/rs-ummi.png` perlu ikut tersedia karena digunakan oleh tampilan aplikasi.

## Akun demonstrasi

Semua akun bawaan menggunakan password **`password`**.

| Role | Email | Penggunaan |
| --- | --- | --- |
| Super Admin | `superadmin@sismat.example` | Seluruh modul dan pengaturan pengguna |
| Admin Aset | `admin.aset@sismat.example` | Inventaris dan operasional aset |
| Petugas Ruangan | `petugas@sismat.example` | Melihat aset dan mengajukan mutasi |
| Teknisi | `budi.teknisi@sismat.example` | Pemeliharaan dan jadwal pekerjaan |
| Pimpinan | `pimpinan@sismat.example` | Laporan dan persetujuan pengajuan |

Akun ini untuk demonstrasi. Ganti password dan sesuaikan data pengguna sebelum aplikasi digunakan untuk data operasional.

## Fitur

- Dashboard dengan statistik aset, grafik kategori/kondisi, aset terbaru, aktivitas, dan informasi pemeliharaan.
- Pengelolaan aset, kategori, gedung, ruangan, dan supplier.
- Pencarian, filter, sorting, pagination, serta modal tambah/edit dan konfirmasi hapus.
- Foto aset dan dokumen pendukung.
- Pemeliharaan dan jadwal dengan tampilan kalender.
- Pengajuan mutasi beserta lokasi asal, tujuan, dan riwayat perpindahan.
- Pengajuan penghapusan aset dengan proses persetujuan.
- Laporan berdasarkan kategori, kondisi, lokasi, dan periode perolehan.
- Export PDF, export CSV yang bisa dibuka di Excel, dan tampilan cetak.
- Login, profil, penggantian password, role, hak akses, dan catatan aktivitas.

Foto dan dokumen disimpan di storage lokal privat. Aksesnya melalui route yang memeriksa izin pengguna, sehingga instalasi ini tidak memerlukan `storage:link` untuk kedua jenis berkas tersebut. Dokumen contoh dari seeder hanya berisi metadata; unggah berkas melalui form edit agar preview dan download tersedia.

Perubahan lokasi aset dilakukan setelah mutasi disetujui dan diselesaikan. Penghapusan operasional aset juga melewati proses persetujuan. Data master yang masih digunakan tidak dapat dihapus.

## Susunan kode

| Lokasi | Isi |
| --- | --- |
| `routes/web.php` | URL aplikasi dan komponen/handler yang dipanggil |
| `app/Livewire` | Logika halaman, form, validasi, query, dan aksi pengguna |
| `app/Models` | Model database dan relasi antartabel |
| `resources/views/livewire` | Tampilan halaman masing-masing modul |
| `resources/views/livewire/partials` | Form dan detail yang dipisahkan per modul |
| `resources/views/components` | Komponen tampilan bersama, seperti modal dan badge |
| `resources/views/layouts` | Layout halaman, sidebar, dan halaman login |
| `database/migrations` | Struktur tabel dan perubahan schema |
| `database/seeders` | Data awal, satu seeder untuk setiap tabel |
| `config/inventory.php` | Nama dan ikon menu |
| `app/Support/ReportExport.php` | Pembuatan PDF dan penanganan nilai CSV |

Sebagai contoh, halaman kategori dibuka melalui route di `routes/web.php`, ditangani oleh `app/Livewire/Categories.php`, dan ditampilkan lewat `resources/views/livewire/categories.blade.php`. Input form ada di `resources/views/livewire/partials/categories-form.blade.php`.

Komponen Livewire langsung menggunakan model untuk membaca dan menyimpan data. Aturan validasi berada di method `rules()`, penyimpanan di `save()`, dan data daftar disiapkan di `render()`.

Pemeriksaan izin berada di `User::hasPermission()`. Daftar izin bawaan masing-masing role berada di `Role::defaultPermissions()`, sedangkan Gate didaftarkan melalui `AppServiceProvider`. Catatan perubahan data disimpan melalui model `ActivityLog`.

## Jika instalasi bermasalah

| Kendala | Yang diperiksa |
| --- | --- |
| `could not find driver` | Pastikan ekstensi PDO SQLite aktif pada PHP yang menjalankan Artisan/server. |
| File SQLite tidak ditemukan | Buat `database/database.sqlite` dan periksa apakah `DB_DATABASE` atau `DB_URL` menimpa koneksi default. |
| `No application encryption key` | Jalankan `php artisan key:generate` setelah `.env` dibuat. |
| Tabel belum ada | Jalankan `php artisan migrate --seed` pada database yang sesuai. |
| CSS/JavaScript tidak termuat | Jalankan `npm run dev` atau `npm run build`; periksa juga file `public/hot` yang tertinggal. |
| Perubahan `.env` belum terbaca | Jalankan `php artisan config:clear`. |
| Tidak dapat menulis file/session | Pastikan proses PHP dapat menulis ke `storage` dan `bootstrap/cache`. |
| Login gagal | Periksa akun seeder, password, dan status aktif pengguna. |
| Download dokumen contoh menghasilkan 404 | Unggah berkas asli terlebih dahulu melalui form edit dokumen. |

Untuk memeriksa route gunakan `php artisan route:list`. Detail error aplikasi dapat dilihat di `storage/logs/laravel.log`.
