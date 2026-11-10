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
├── frontend/              Semua tampilan: Blade, CSS, JS, dan konfigurasi Vite
│   ├── views/
│   │   ├── site/          Halaman website publik (beranda, artikel, program, donasi, tentang)
│   │   ├── admin/         Halaman panel admin, satu folder per menu (auth/ untuk login dan lupa password)
│   │   └── components/    Komponen Blade: <x-site.*>, <x-admin.*>, <x-layouts.*>
│   ├── css/app.css        Tailwind CSS
│   ├── js/                app.js (umum) dan editor.js (editor tulisan di admin)
│   └── package.json       npm: Vite, Tailwind, Tiptap
├── backend/               Aplikasi Laravel: route, controller, model, database, API, test
│   ├── app/Http/Controllers/   Site/ (website publik), Admin/ (panel admin), Api/ (API token)
│   ├── app/Http/Requests/      Validasi form, dikelompokkan sama seperti controller
│   ├── public/            Root web server: index.php, gambar statis (images/), hasil build Vite (build/)
│   └── tests/Feature/     Site/, Admin/, Api/, Seeders/
├── docs/                  Ekspor desain dari Figma (tidak ikut di-commit)
└── .github/               Workflow GitHub Actions
```

Tips mencari file: halaman publik `/donasi/formulir` ada di `frontend/views/site/donasi/formulir.blade.php` dan ditangani `backend/app/Http/Controllers/Site/SiteController.php`. Menu admin "Kegiatan panti" ada di `frontend/views/admin/kegiatan-panti/`, `Controllers/Admin/KegiatanPantiController.php`, dan `tests/Feature/Admin/KegiatanPantiTest.php`.

Gambar statis tetap di `backend/public/images` karena folder `public` adalah root web server, dan alamat `images/...` juga tersimpan di database (gambar artikel dan program contoh).

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
| Anak panti | Data anak, bisa diekspor. Tombol **Scan KK / Akta** membaca foto Kartu Keluarga atau akta kelahiran dengan AI, lalu data anak diperiksa di form tinjauan sebelum disimpan |
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

   `composer setup` menjalankan `composer install`, `key:generate`, `migrate`, lalu `npm install` dan `npm run build` di folder `frontend`. `storage:link` dibutuhkan supaya gambar berita, program, dan galeri bisa tampil.
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

   Kalau sedang mengubah tampilan, jalankan `composer dev` dari folder `backend` (atau `npm run dev:all` dari folder `frontend`). Perintah ini menjalankan server Laravel, queue, dan Vite sekaligus, jadi perubahan Blade, CSS, dan JS langsung terlihat. Hasil build Vite ditulis ke `backend/public/build`.

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
GEMINI_API_KEY=...      # untuk membaca bukti transfer dan scan KK/akta; tanpa ini kedua fitur itu nonaktif
```

Scan KK/akta dan bukti transfer dibaca oleh Gemini (Google): berkasnya dikirim ke Google. Pakai API key dari project Google Cloud yang billing-nya aktif untuk data asli, karena di tingkat gratis Google boleh memakai data yang dikirim untuk mengembangkan produknya. Foto KK/akta tidak disimpan di server; hasil bacanya hanya mengisi form, dan data tersimpan setelah pengurus menekan Simpan.

Folder `frontend` harus ikut di-upload ke server, sejajar dengan folder `backend`, karena tampilan Blade dibaca dari sana. Dari folder `backend`, jalankan:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
npm ci --prefix ../frontend && npm run build --prefix ../frontend
php artisan optimize
```

Opsional:

- `ADMIN_DOMAIN=admin.domain-panti.org` membuat panel admin hanya bisa dibuka dari subdomain itu.
- `API_DOMAIN=api.domain-panti.org` memindahkan API ke subdomain sendiri.

## Alur kerja Git

- Kerjakan perubahan di branch `dev`.
- Pull request ke `main` **hanya boleh dari `dev`**. Aturan ini dicek otomatis oleh workflow `PR Source Check`.
