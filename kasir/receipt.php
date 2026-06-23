<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';
require_login();
$transactionId = $_SESSION['last_transaction_id'] ?? null;
if (!$transactionId) {
    header('Location: /kasir/transaksi.php');
    exit;
}
$stmt = $pdo->prepare('SELECT t.*, u.username AS kasir FROM transaksi t LEFT JOIN users u ON t.kasir_id = u.id WHERE t.id = :id');
$stmt->execute(['id' => $transactionId]);
$transaction = $stmt->fetch();
$details = [];
if ($transaction) {
    $details = $pdo->prepare('SELECT d.*, p.nama FROM detail_transaksi d LEFT JOIN produk p ON d.produk_id = p.id WHERE d.transaksi_id = :id');
    $details->execute(['id' => $transactionId]);
    $details = $details->fetchAll();
}
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="card p-4">
    <h1 class="display-6">Struk Pembayaran</h1>
    <?php if (!$transaction): ?>
      <p>Transaksi tidak ditemukan.</p>
    <?php else: ?>
      <div class="mb-3"><strong>No. Transaksi:</strong> <?= safe($transaction['id']) ?></div>
      <div class="mb-3"><strong>Kasir:</strong> <?= safe($transaction['kasir']) ?></div>
      <div class="mb-3"><strong>Metode:</strong> <?= safe($transaction['payment_method']) ?></div>
      <div class="mb-3"><strong>Status:</strong> <?= safe($transaction['status']) ?></div>
      <div class="mb-3"><strong>Tanggal:</strong> <?= safe($transaction['created_at']) ?></div>
      <div class="table-responsive">
        <table class="table table-dark table-striped align-middle">
          <thead><tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
          <tbody>
            <?php foreach ($details as $item): ?>
              <tr>
                <td><?= safe($item['nama']) ?></td>
                <td><?= $item['qty'] ?></td>
                <td>Rp <?= number_format($item['harga'],0,',','.') ?></td>
                <td>Rp <?= number_format($item['subtotal'],0,',','.') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="text-end mt-4">
        <strong>Total: Rp <?= number_format($transaction['total'],0,',','.') ?></strong>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
