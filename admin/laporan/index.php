<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_login();
if (($_SESSION['user']['role'] ?? '') !== 'Admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Forbidden');
}
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');
$stmt = $pdo->prepare('SELECT COUNT(*) AS total_transaksi, COALESCE(SUM(total),0) AS total_pendapatan FROM transaksi WHERE created_at BETWEEN :start AND :end');
$stmt->execute(['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59']);
$summary = $stmt->fetch();
$transactions = $pdo->prepare('SELECT t.*, u.username AS kasir FROM transaksi t LEFT JOIN users u ON t.kasir_id = u.id WHERE t.created_at BETWEEN :start AND :end ORDER BY t.id DESC');
$transactions->execute(['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59']);
$transactions = $transactions->fetchAll();
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-shell">
  <?php include __DIR__ . '/../sidebar.php'; ?>
  <main class="admin-main">
    <div class="page-head">
      <div>
        <h1 class="page-title">Laporan Transaksi</h1>
        <p class="text-muted mb-0">Pantau penjualan dan pendapatan toko.</p>
      </div>
    </div>
    <form class="row g-3 mb-4" method="get">
      <div class="col-md-4">
        <label class="form-label">Dari tanggal</label>
        <input type="date" name="start" value="<?= safe($start) ?>" class="form-control text-input">
      </div>
      <div class="col-md-4">
        <label class="form-label">Sampai tanggal</label>
        <input type="date" name="end" value="<?= safe($end) ?>" class="form-control text-input">
      </div>
      <div class="col-md-4 align-self-end">
        <button class="btn btn-primary button-pill">Filter</button>
      </div>
    </form>
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="stat-card">
          <div class="label">Total Transaksi</div>
          <p class="value"><?= number_format($summary['total_transaksi']) ?></p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="stat-card">
          <div class="label">Pendapatan</div>
          <p class="value">Rp <?= number_format($summary['total_pendapatan'], 0, ',', '.') ?></p>
        </div>
      </div>
    </div>
    <div class="card p-4">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr><th>ID</th><th>Kasir</th><th>Total</th><th>Metode</th><th>Status</th><th>Tanggal</th></tr>
          </thead>
          <tbody>
            <?php foreach ($transactions as $trx): ?>
              <tr>
                <td><?= $trx['id'] ?></td>
                <td><?= safe($trx['kasir'] ?? 'Unknown') ?></td>
                <td>Rp <?= number_format($trx['total'], 0, ',', '.') ?></td>
                <td><?= safe($trx['payment_method']) ?></td>
                <td><?= safe($trx['status']) ?></td>
                <td><?= safe($trx['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php';
