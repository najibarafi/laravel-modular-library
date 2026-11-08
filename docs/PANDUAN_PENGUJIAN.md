# Panduan Pengujian Aplikasi

Dokumen ini berisi langkah-langkah menjalankan aplikasi, akun demo, skenario uji manual,
dan pengujian otomatis.

---

## 1. Prasyarat

| Kebutuhan | Versi yang diuji |
|---|---|
| PHP | 8.5.0 |
| Composer | 2.8.12 |
| Node.js | 24.18.0 |
| Database | SQLite (default) |
| Framework | Laravel 13.38.0 |

---

## 2. Instalasi

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

> Di Windows PowerShell, gunakan `npm.cmd install` dan `npm.cmd run build` bila
> `npm.ps1` diblokir execution policy.

---

## 3. Akun Demo

| Peran | Email | Password | Hak akses |
|---|---|---|---|
| Admin | `admin@example.com` | `password123` | Semua fitur (CRUD, peminjaman, pengembalian, denda, restore) |
| User biasa | `user@example.com` | `password123` | Hanya membaca (index/detail) |

Data awal hasil seeder:

- 40 buku, 8 penulis, 5 penerbit, 15 kategori
- 15 anggota, 3 role
- 11 peminjaman, 12 item peminjaman
- 2 denda (total Rp 6.000), 1 peminjaman aktif yang terlambat

---

## 4. Skenario Uji Manual

### Skenario 1 — Login dan Hak Akses

1. Login sebagai `admin@example.com`.
2. Buka menu **Perpustakaan → Buku**.
3. Pastikan tombol **+ Tambah Buku**, **Edit**, **Hapus** terlihat.
4. Logout, login sebagai `user@example.com`.
5. Buka halaman yang sama.

**Hasil yang diharapkan:** user biasa hanya melihat daftar/detail. Membuka
`/books/create` secara langsung menghasilkan **403 Forbidden**.

---

### Skenario 2 — Filter dan Pencarian Buku

1. Login sebagai admin.
2. Buka **Buku**.
3. Isi kata kunci pada kolom pencarian, pilih kategori/penulis/penerbit, centang
   **Hanya tersedia**, lalu klik **Terapkan Filter**.
4. Klik **Reset** untuk mengembalikan daftar.

**Hasil yang diharapkan:** daftar terfilter, URL menyimpan query string, dan pagination
tetap membawa filter.

---

### Skenario 3 — Peminjaman Berhasil (Transaksi)

1. Buka **Peminjaman → + Buat Peminjaman**.
2. Pilih anggota yang masih memiliki kuota (misal `Elias Zieme — 0/3`).
3. Pilih buku dengan stok > 0, jumlah `1` atau lebih.
4. Klik **Simpan Peminjaman**.

**Hasil yang diharapkan:**

- Redirect ke halaman detail peminjaman dengan pesan sukses.
- Nomor peminjaman berformat `LN-YYYYMMDD-XXXXXX`.
- Status **Dipinjam**, jatuh tempo 7 hari setelah tanggal pinjam.
- Stok buku berkurang sesuai jumlah.
- `active_loans` anggota bertambah dan status berubah menjadi `borrowing`.
- Riwayat status mencatat `borrowed` beserta nama admin.
- File `storage/logs/library-events.log` bertambah baris `BookBorrowed`.

---

### Skenario 4 — Rollback karena Stok Tidak Cukup

Cara termudah: gunakan test otomatis (Skenario 9) atau coba lewat UI dengan memilih
jumlah melebihi stok yang tersedia.

**Hasil yang diharapkan:** muncul pesan error, tidak ada record peminjaman baru,
stok dan status anggota tidak berubah (rollback total).

Contoh pesan: `Stok buku '...' tidak mencukupi. Tersedia 1, diminta 3.`

---

### Skenario 5 — Pengembalian Tepat Waktu

1. Buka detail peminjaman yang masih aktif.
2. Klik **Kembalikan Buku** lalu konfirmasi.

**Hasil yang diharapkan:**

- Status berubah menjadi **Dikembalikan**.
- Stok buku kembali bertambah.
- `active_loans` anggota berkurang dan status kembali `active` bila tidak ada pinjaman lain.
- Tidak ada denda (`total_fine = 0`).
- Log `BookReturned` tercatat.

---

### Skenario 6 — Pengembalian Terlambat dan Denda

Data seeder sudah menyiapkan dua peminjaman yang dikembalikan terlambat 3 hari.
Buka **Denda** untuk melihat hasilnya.

**Hasil yang diharapkan:**

- Dua baris denda, masing-masing 3 hari × Rp 1.000 = **Rp 3.000**.
- Total denda belum dibayar **Rp 6.000**.
- Status denda `unpaid`.
- Klik **Bayar** → konfirmasi → status menjadi `paid`, `paid_at` terisi, total belum
  dibayar berkurang.

Perhitungan denda menggunakan Value Object `Denda`:

```php
$denda = Denda::between($loan->due_at, now());
// 3 hari terlambat => 3 x Rp 1.000 = Rp 3.000
```

---

### Skenario 7 — Soft Delete dan Restore

1. Buka **Buku**, klik **Hapus** pada salah satu buku, konfirmasi.
2. Buku hilang dari daftar aktif.
3. Klik **Lihat Buku Terhapus**.
4. Klik **Restore**.

**Hasil yang diharapkan:**

- Buku tidak benar-benar dihapus dari database (`deleted_at` terisi).
- Muncul di daftar terhapus dengan status **Terhapus**.
- Setelah restore, buku kembali ke daftar aktif dan muncul pesan sukses.
- Perilaku yang sama berlaku untuk **Anggota** dan **Peminjaman**.

---

### Skenario 8 — Relasi Lanjutan

1. Buka detail buku.
2. Perhatikan daftar kategori beserta `added_at` (pivot Many-to-Many).
3. Buka **Kategori**; perhatikan kolom **Induk** dan **Sub-kategori** (self-referencing).
4. Buka detail peminjaman; perhatikan daftar item (HasMany) dan snapshot judul/ISBN.
5. Buka detail anggota; perhatikan role (Many-to-Many dengan `assigned_at`) dan riwayat
   keanggotaan (HasMany).

---

### Skenario 9 — Pengujian Otomatis

```bash
php artisan test
```

Output terakhir:

```text
Tests:    57 passed (181 assertions)
Duration: ~8.5s
```

Rincian file test:

| File | Cakupan |
|---|---|
| `tests/Unit/IsbnTest.php` | Validasi ISBN-10/13, checksum, prefix, normalisasi |
| `tests/Unit/DendaTest.php` | Perhitungan denda, pembulatan hari, penolakan nilai negatif |
| `tests/Feature/Library/LoanTransactionTest.php` | Peminjaman, rollback, event, pengembalian + denda, anggota suspended |
| `tests/Feature/Library/SoftDeleteRestoreTest.php` | Soft delete/restore Book, Member, Loan; restore via route; role restriction |
| `tests/Feature/Library/QueryOptimizationTest.php` | Batas jumlah query eager loading; scope `available`, `search`, `inCategory` |
| `tests/Feature/Library/PageSmokeTest.php` | Semua halaman utama merender 200 untuk admin; user biasa mendapat 403 pada form tulis |

Contoh menjalankan subset:

```bash
php artisan test --filter=Isbn
php artisan test --filter=Denda
php artisan test --filter=LoanTransaction
php artisan test --filter=QueryOptimization
```

---

### Skenario 10 — Demo Optimasi Query

```bash
php artisan library:query-demo
```

**Hasil yang diharapkan:** tabel perbandingan query N+1 vs eager loading
(12 vs 2 untuk peminjaman; 81 vs 3 untuk buku). Detail ada di
`docs/QUERY_OPTIMIZATION.md`.

---

## 5. Bukti Pendukung

| Bukti | Lokasi |
|---|---|
| Screenshot UI | `docs/screenshots/01`–`14` |
| Log domain event | `storage/logs/library-events.log` |
| Output query demo | `docs/QUERY_OPTIMIZATION.md` |
| Source test | `tests/Unit/`, `tests/Feature/Library/` |
| Arsitektur & ERD | `docs/ARSITEKTUR.md` |

---

## 6. Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| `Class "Modules\...\Illuminate\Http\Request" does not exist` | Type hint `Request` tanpa import di namespace modul | Tambahkan `use Illuminate\Http\Request;` (sudah diperbaiki) |
| `/books/create` menghasilkan 404 | Route `books/{book}` terdaftar sebelum `books/create` | Daftarkan group admin (create/store) sebelum route read-only (sudah diperbaiki) |
| `npm.ps1 cannot be loaded` | Execution policy PowerShell | Gunakan `npm.cmd` |
| Vite gagal build `@rolldown/binding-win32-x64-msvc` | Optional dependency belum terpasang | `npm.cmd install` lalu `npm.cmd run build` |
| Factory `Modules\...\Factory` tidak ditemukan | Resolver factory default memakai namespace `App\Models` | `Factory::guessFactoryNamesUsing()` di `AppServiceProvider` (sudah diperbaiki) |
| Denda seeder selalu 0 | `Collection::slice()` mempertahankan key sehingga indeks tidak mulai dari 0 | Gunakan `->values()` sebelum loop (sudah diperbaiki) |
