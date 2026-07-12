<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';
require_login();

$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum(array_column($cart, 'quantity'));
$total = array_reduce($cart, function ($carry, $item) {
  return $carry + (($item['harga'] ?? 0) * ($item['quantity'] ?? 0));
}, 0);

$produkCount = $pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn();
$trxToday = $pdo->query('SELECT COUNT(*) FROM transaksi WHERE DATE(created_at) = CURDATE()')->fetchColumn();
$produkTerbaru = $pdo->query('SELECT id, nama, harga, stok FROM produk ORDER BY id DESC LIMIT 4')->fetchAll();
$trxTerbaru = $pdo->query('SELECT t.id, u.username AS kasir, t.total, t.status, t.created_at FROM transaksi t LEFT JOIN users u ON u.id = t.kasir_id ORDER BY t.id DESC LIMIT 5')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-shell">
  <main class="admin-main">
    <div class="page-head">
      <div>
        <h1 class="page-title">Dashboard Kasir</h1>
        <p class="text-muted mb-0">Kelola penjualan harian dengan lebih cepat dan rapi.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/Kasir/kasir/transaksi.php" class="btn btn-primary button-pill">Buka Transaksi</a>
        <a href="/Kasir/kasir/payment.php" class="btn btn-outline-secondary button-pill">Pembayaran</a>
      </div>
    </div>

    <div class="stat-grid">
      <div class="stat-card">
        <div class="label">Item di Keranjang</div>
        <p class="value"><?= number_format($cartCount) ?></p>
      </div>
      <div class="stat-card">
        <div class="label">Total Belanja</div>
        <p class="value">Rp <?= number_format($total, 0, ',', '.') ?></p>
      </div>
      <div class="stat-card">
        <div class="label">Produk Tersedia</div>
        <p class="value"><?= number_format($produkCount) ?></p>
      </div>
      <div class="stat-card">
        <div class="label">Transaksi Hari Ini</div>
        <p class="value"><?= number_format($trxToday) ?></p>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card p-4 mb-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Aksi Cepat</h5>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <a href="/Kasir/kasir/transaksi.php" class="btn btn-primary w-100 py-4">Mulai Transaksi</a>
            </div>
            <div class="col-md-6">
              <a href="/Kasir/kasir/payment.php" class="btn btn-outline-secondary w-100 py-4">Lanjut ke Pembayaran</a>
            </div>
          </div>
        </div>

        <div class="card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Produk Terbaru</h5>
          </div>
          <div class="row g-3">
            <?php foreach ($produkTerbaru as $p): ?>
              <div class="col-md-6">
                <div class="border rounded-4 p-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <strong><?= safe($p['nama']) ?></strong>
                    <span class="badge bg-success">Stok <?= (int)$p['stok'] ?></span>
                  </div>
                  <p class="mb-0 mt-2 text-muted">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card p-4 mb-4">
          <h5 class="mb-0">Status Keranjang</h5>
          <div class="mt-3">
            <?php if (!empty($cart)): ?>
              <ul class="list-group list-group-flush">
                <?php foreach ($cart as $item): ?>
                  <li class="list-group-item px-0">
                    <div class="d-flex justify-content-between align-items-center">
                      <span><?= safe($item['nama'] ?? 'Item') ?></span>
                      <span class="badge bg-primary">x<?= (int)($item['quantity'] ?? 0) ?></span>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="text-muted mb-0">Belum ada item di keranjang.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="card p-4">
          <h5 class="mb-0">Transaksi Terbaru</h5>
          <ul class="list-group list-group-flush mt-3">
            <?php foreach ($trxTerbaru as $trx): ?>
              <li class="list-group-item px-0">
                <div class="d-flex justify-content-between">
                  <span>#<?= $trx['id'] ?> · <?= safe($trx['kasir'] ?? 'Kasir') ?></span>
                  <span class="badge bg-warning text-dark"><?= safe($trx['status']) ?></span>
                </div>
                <small class="text-muted">Rp <?= number_format($trx['total'], 0, ',', '.') ?></small>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include __DIR__ . '/../includes/footer.php';
