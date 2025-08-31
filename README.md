# DocSum - AI Document Summarizer

## Deskripsi
Aplikasi web berbasis Laravel + Livewire untuk merangkum dokumen (PDF, DOCX, TXT), teks, atau artikel dari link menggunakan AI Granite (Replicate API). UI menggunakan Bootstrap dan SweetAlert2 untuk feedback loading.

---

## Cara Setup di Local Machine

### 1. Clone Repository
```bash
git clone https://github.com/xdyaksa/AI-Sumarizer-Hacktiv8-Capstone.git
cd doc-sum
```

### 2. Install Dependency
#### PHP & Composer
```bash
composer install
```
#### Node.js & NPM
```bash
npm install
```

### 3. Copy & Edit File Environment
```bash
cp .env.example .env
```
Edit `.env` sesuai kebutuhan (database, mail, dll).

### 4. Generate Key
```bash
php artisan key:generate
```

### 5. Migrasi Database
```bash
php artisan migrate
```

### 6. Build Asset Frontend
```bash
npm run build
```

### 7. Jalankan Server
```bash
php artisan serve
```
Akses di browser: `http://localhost:8000`

---

## Cara Memasang Token API Replicate

1. Dapatkan token API dari https://replicate.com/account/api-tokens
2. Buka file `.env` di root project.
3. Tambahkan baris berikut:
```
REPLICATE_API_TOKEN=token_anda_disini
```
4. Simpan file `.env`.
5. Pastikan token sudah terpakai di kode (cek di `app/Livewire/DocumentSummarizer.php` atau config).

---

## Troubleshooting
- Jika asset (CSS/JS) tidak tampil, jalankan `npm run build` lagi.
- Jika migrasi gagal, cek koneksi database di `.env`.
- Jika API Replicate error, pastikan token valid dan ada saldo.
- **Jika error ZipArchive** (misal: `Class 'ZipArchive' not found` atau gagal ekstrak DOCX/PDF):
    - Pastikan ekstensi `php-zip` sudah aktif di PHP Anda.
    - Di Windows: buka `php.ini`, cari dan hilangkan tanda `;` di depan `extension=zip` lalu restart web server.
    - Di Linux: jalankan `sudo apt install php-zip` lalu restart web server.
