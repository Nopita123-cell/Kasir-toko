<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';
require_login();
if (($_SESSION['user']['role'] ?? '') !== 'Admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Forbidden');
}

$stats = [
  'produk' => $pdo->query('SELECT COUNT(*) AS total FROM produk')->fetchColumn(),
  'kategori' => $pdo->query('SELECT COUNT(*) AS total FROM kategori')->fetchColumn(),
  'transaksi' => $pdo->query('SELECT COUNT(*) AS total FROM transaksi')->fetchColumn(),
  'user' => $pdo->query('SELECT COUNT(*) AS total FROM users')->fetchColumn(),
];
$latest = $pdo->query('SELECT t.id, u.username AS kasir, t.total, t.payment_method, t.status, t.created_at FROM transaksi t LEFT JOIN users u ON u.id = t.kasir_id ORDER BY t.id DESC LIMIT 5')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="admin-main">
    <div class="page-head">
      <div>
        <h1 class="page-title">Dashboard Admin</h1>
        <p class="text-muted mb-0">Ringkasan cepat operasional toko Anda.</p>
      </div>
      <a href="/Kasir/admin/laporan/index.php" class="btn btn-primary button-pill">Lihat Laporan</a>
    </div>

    <div class="stat-grid">
      <div class="stat-card">
        <div class="label">Total Produk</div>
        <p class="value"><?= number_format($stats['produk']) ?></p>
      </div>
      <div class="stat-card">
        <div class="label">Kategori</div>
        <p class="value"><?= number_format($stats['kategori']) ?></p>
      </div>
      <div class="stat-card">
        <div class="label">Transaksi</div>
        <p class="value"><?= number_format($stats['transaksi']) ?></p>
      </div>
      <div class="stat-card">
        <div class="label">User</div>
        <p class="value"><?= number_format($stats['user']) ?></p>
      </div>
    </div>

    <div class="card p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Transaksi Terbaru</h5>
        <a href="/Kasir/admin/laporan/index.php" class="btn btn-outline-secondary btn-sm">Detail</a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Kasir</th>
              <th>Total</th>
              <th>Metode</th>
              <th>Status</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($latest as $trx): ?>
              <tr>
                <td><?= $trx['id'] ?></td>
                <td><?= safe($trx['kasir'] ?? 'Unknown') ?></td>
                <td>Rp <?= number_format($trx['total'], 0, ',', '.') ?></td>
                <td><?= safe($trx['payment_method']) ?></td>
                <td><span class="badge <?= $trx['status'] === 'Lunas' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= safe($trx['status']) ?></span></td>
                <td><?= safe($trx['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php include __DIR__ . '/../includes/footer.php';