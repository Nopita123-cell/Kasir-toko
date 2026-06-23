<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (($_SESSION['user']['role'] ?? '') !== 'Admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Forbidden');
}
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="display-6">Dashboard Admin</h1>
      <p class="text-muted">Kelola pengguna, produk, kategori, dan laporan.</p>
    </div>
  </div>
  <div class="row gy-4">
    <div class="col-md-4">
      <div class="plan-card">
        <h5>Kelola User</h5>
        <p>Tambah, edit, atau hapus user.</p>
        <a href="/admin/user/index.php" class="btn btn-outline-light button-pill">Buka</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="plan-card">
        <h5>Kelola Produk</h5>
        <p>Inventaris produk, stok, harga, dan kategori.</p>
        <a href="/admin/produk/index.php" class="btn btn-outline-light button-pill">Buka</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="plan-card">
        <h5>Kelola Kategori</h5>
        <p>Atur kategori produk untuk proses kasir.</p>
        <a href="/admin/kategori/index.php" class="btn btn-outline-light button-pill">Buka</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="plan-card">
        <h5>Laporan</h5>
        <p>Lihat ringkasan transaksi harian dan bulanan.</p>
        <a href="/admin/laporan/index.php" class="btn btn-outline-light button-pill">Buka</a>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
