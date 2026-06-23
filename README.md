# Web Kasir (POS)

Aplikasi kasir berbasis web dengan panel Admin dan Kasir, menggunakan PHP Native, MySQL, dan Bootstrap 5.

## Fitur
- Login Admin / Kasir
- Dashboard Admin
- Kelola Produk, Kategori, User
- Transaksi Kasir dengan keranjang
- Pembayaran dan struk
- Laporan transaksi

## Struktur
- `config/`: koneksi database dan konfigurasi
- `includes/`: header, footer, auth, helper
- `admin/`: halaman manajemen admin
- `kasir/`: halaman kasir
- `assets/css/theme.css`: tema custom
- `db/schema.sql`: skema database dan seed awal

## Setup di XAMPP
1. Salin folder `Kasir` ke `htdocs`.
2. Jalankan Apache dan MySQL di XAMPP.
3. Buka `http://localhost/phpmyadmin`.
4. Import `db/schema.sql`.
5. Pastikan database `kasir_db` dibuat dan tabel terisi.
6. Buka `http://localhost/Kasir/login.php`.

## Akun Awal
- Username: `admin`
- Password: `admin123`

## Midtrans
- Untuk integrasi nyata, isi `config/config.php` dengan `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY`.
 - Untuk integrasi nyata, isi `config/config.php` dengan `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY`.
	 Contoh: buka dashboard Midtrans (sandbox) -> ambil server key & client key -> masukkan di `config/config.php`.
	 Setelah diisi, pada halaman kasir di `Bayar` akan muncul tombol `Bayar via Midtrans (Sandbox)`.

## Catatan
- Halaman admin hanya dapat diakses oleh `Admin`.
- Kasir dapat melakukan transaksi dan melihat struk.
- Shortcuts kasir: tekan `/` untuk fokus pencarian, gunakan `ArrowUp/ArrowDown` atau `j/k` untuk menavigasi hasil, `Enter` untuk menambah produk.
