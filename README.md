# SAHABAT

Website dan panel admin **Panti Asuhan YASIBU**. Pengunjung bisa melihat profil panti, program, artikel, galeri, dan berdonasi. Pengurus bisa mengelola data anak, kesehatan anak, donasi, dan isi website dari panel admin.

## Teknologi

- PHP 8.3 dan Laravel 13
- Blade, Tailwind CSS v4, Vite
- MySQL
- Laravel Sanctum untuk token API
- PHPUnit untuk test

## Struktur repo

```
SAHABAT/
├── backend/     Aplikasi Laravel: website publik, panel admin, dan API
├── frontend/    Belum dipakai. Semua tampilan ada di backend/resources/views (Blade)
├── docs/        Ekspor desain dari Figma (tidak ikut di-commit)
└── .github/     Workflow GitHub Actions
```

## Fitur

### Website publik

- **Beranda**: ringkasan panti, program, dan artikel terbaru
- **Tentang Kami**: profil lembaga, visi misi, pengurus, anak asuh, galeri, dan kontak. Semua isinya diatur dari menu Profil panti di admin
- **Program** dan **Artikel**
- **Donasi**: formulir donasi (zakat, infak, sedekah, wakaf, beasiswa), info rekening, cara bayar QRIS, dan daftar kebutuhan panti yang paling mendesak
- **Formulir kontak**: pesan masuk ke menu Pesan di admin

### Panel admin (`/admin`)

| Menu | Isi |
| --- | --- |
| Dashboard | Ringkasan data panti |
| Anak panti | Data anak, bisa diekspor |
| Kesehatan anak | Catatan timbang dan analisis status gizi (dihitung dengan rumus, tanpa AI) |
| Pengasuh, Wali anak | Data pengasuh dan wali |
| Kegiatan panti | Kegiatan beserta fotonya |
| Donasi | Verifikasi donasi masuk, bisa diekspor |
| Kebutuhan panti | Daftar kebutuhan dan prioritasnya |
| Berita, Program, Galeri | Isi website publik |
| Pesan | Pesan dari formulir kontak |
| Profil panti | Teks halaman Tentang Kami, kontak, dan media sosial |
| Kelola akun | Tambah atau nonaktifkan akun (khusus admin) |
| Akun saya | Ubah profil dan password sendiri |

Ada dua peran:

- **admin**: bisa mengakses semua menu, termasuk menghapus data dan mengelola akun.
- **pengurus**: bisa menambah dan mengubah data, tapi tidak bisa menghapus dan tidak bisa membuka Kelola akun.

### API (`/api`)

API memakai token Sanctum untuk aplikasi lain, misalnya aplikasi mobile.

- `POST /api/auth/login` untuk mendapatkan token
- `GET /api/auth/me`, `PUT /api/auth/password`, `POST /api/auth/logout`
- CRUD untuk `anak-panti`, `wali-anak`, `pengasuh`, `kegiatan-panti`, dan `users` (khusus admin)

Kirim token di header `Authorization: Bearer <token>`. Token berlaku 7 hari.

## Menjalankan di komputer sendiri

Yang perlu disiapkan: PHP 8.3, Composer, Node.js, dan MySQL. Di Windows paling mudah pakai [Laragon](https://laragon.org).

1. Buat database MySQL kosong, misalnya `demo_pantiyasibu`.
2. Masuk ke folder `backend` lalu siapkan file `.env`:

   ```bash
   cd backend
   cp .env.example .env
   ```

   Sesuaikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`.
3. Install dependensi, buat tabel, dan build tampilan:

   ```bash
   composer setup
   php artisan storage:link
   ```

   `composer setup` menjalankan `composer install`, `key:generate`, `migrate`, `npm install`, dan `npm run build`. `storage:link` dibutuhkan supaya gambar berita, program, dan galeri bisa tampil.
4. (Opsional) isi data awal:

   ```bash
   php artisan db:seed
   ```

   Perintah ini membuat dua akun untuk development: `admin@sahabat.test` dan `pengurus@sahabat.test`, keduanya dengan password `password`. **Ganti password atau hapus akun ini sebelum website dipakai sungguhan.**

   Data anak panti diambil dari `database/sql/demo_pantiyasibu.sql`. File ini berisi data pribadi anak, jadi **tidak ada di repo dan jangan pernah di-commit**. Minta filenya ke pengurus proyek. Tanpa file itu, seeder tetap jalan tapi data anaknya kosong.
5. Jalankan server:

   ```bash
   php artisan serve
   ```

   Buka http://localhost:8000. Panel admin ada di http://localhost:8000/login.

   Kalau sedang mengubah tampilan, jalankan `composer dev`. Perintah ini menjalankan server Laravel dan Vite sekaligus, jadi perubahan CSS/JS langsung terlihat.

### Membuat akun admin baru

```bash
php artisan tinker --execute 'App\Models\User::create(["name" => "admin", "email" => "admin@contoh.org", "password" => "PasswordKuat-1", "role" => "admin", "is_active" => true]);'
```

Password otomatis di-hash. Syaratnya minimal 8 karakter dan harus ada huruf besar, huruf kecil, dan angka. Login bisa memakai username (`name`) atau email.

## Test

```bash
cd backend
php artisan test
```

Test memakai SQLite di memori, jadi tidak menyentuh database MySQL.

Sebelum commit, rapikan kode dengan:

```bash
vendor/bin/pint --dirty
```

## Keamanan

- Semua query memakai Eloquent atau query builder dengan parameter binding, jadi aman dari SQL injection.
- Password di-hash dengan **Argon2id** dengan salt acak per password. Hash bcrypt lama otomatis diperbarui saat pemiliknya login.
- Login dikunci 15 menit setelah 5 kali gagal.
- Header keamanan dikirim di setiap halaman: Content-Security-Policy dengan nonce, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, dan HSTS saat memakai HTTPS.
- Sesi dienkripsi. Akun yang dinonaktifkan langsung keluar, dan mengganti password akan mengeluarkan sesi di perangkat lain.
- Upload foto dibatasi jenis dan ukurannya.
- Teks dari admin di-escape saat ditampilkan.

## Deploy ke server

Atur nilai berikut di `.env` server:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-panti.org
SESSION_SECURE_COOKIE=true
MAIL_MAILER=smtp        # supaya email lupa password benar-benar terkirim
```

Lalu jalankan:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
npm ci && npm run build
php artisan optimize
```

Opsional:

- `ADMIN_DOMAIN=admin.domain-panti.org` membuat panel admin hanya bisa dibuka dari subdomain itu.
- `API_DOMAIN=api.domain-panti.org` memindahkan API ke subdomain sendiri.

## Alur kerja Git

- Kerjakan perubahan di branch `dev`.
- Pull request ke `main` **hanya boleh dari `dev`**. Aturan ini dicek otomatis oleh workflow `PR Source Check`.
