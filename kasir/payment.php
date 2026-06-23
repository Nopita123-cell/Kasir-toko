<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_login();
$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    header('Location: /kasir/kasir/transaksi.php');
    exit;
}
$total = array_reduce($cart, fn($sum, $item) => $sum + ($item['harga'] * $item['quantity']), 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'Tunai';
    $stmt = $pdo->prepare('INSERT INTO transaksi (kasir_id, total, payment_method, status) VALUES (:kasir_id, :total, :payment_method, :status)');
    $stmt->execute([
        'kasir_id' => $_SESSION['user']['id'],
        'total' => $total,
        'payment_method' => $payment_method,
        'status' => 'Lunas',
    ]);
    $transactionId = $pdo->lastInsertId();
    $stmtDetail = $pdo->prepare('INSERT INTO detail_transaksi (transaksi_id, produk_id, qty, harga, subtotal) VALUES (:tid, :pid, :qty, :harga, :subtotal)');
    foreach ($cart as $item) {
        $stmtDetail->execute([
            'tid' => $transactionId,
            'pid' => $item['id'],
            'qty' => $item['quantity'],
            'harga' => $item['harga'],
            'subtotal' => $item['harga'] * $item['quantity'],
        ]);
        $pdo->prepare('UPDATE produk SET stok = stok - :qty WHERE id = :id')->execute(['qty' => $item['quantity'], 'id' => $item['id']]);
    }
    $pdo->prepare('INSERT INTO payments (transaksi_id, amount, method, status) VALUES (:tid, :amount, :method, :status)')->execute([
        'tid' => $transactionId,
        'amount' => $total,
        'method' => $payment_method,
        'status' => 'paid',
    ]);
    unset($_SESSION['cart']);
    $_SESSION['last_transaction_id'] = $transactionId;
    redirect('/kasir/receipt.php');
}
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card p-4">
        <h1 class="display-6">Pembayaran</h1>
        <p>Total yang harus dibayar: <strong>Rp <?= number_format($total,0,',','.') ?></strong></p>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Metode Pembayaran</label>
            <select name="payment_method" class="form-select text-input">
              <option>Tunai</option>
              <option>QRIS</option>
              <option>Transfer Bank</option>
              <option>E-wallet</option>
            </select>
          </div>
          <button class="btn btn-primary button-pill w-100">Bayar</button>
        </form>
        <?php if (!empty(MIDTRANS_CLIENT_KEY)): ?>
          <div class="mt-3">
            <button id="pay-midtrans" class="btn btn-outline-primary w-100 button-pill">Bayar via Midtrans (Sandbox)</button>
          </div>
          <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
          <script>
            document.getElementById('pay-midtrans').addEventListener('click', function(){
              this.disabled = true;
              fetch('/midtrans/create_snap.php').then(r => r.json()).then(function(res){
                if (res.token) {
                  snap.pay(res.token, {
                    onSuccess: function(result){ location.href = '/kasir/receipt.php'; },
                    onPending: function(result){ alert('Pembayaran pending'); },
                    onError: function(result){ alert('Error: ' + JSON.stringify(result)); }
                  });
                } else {
                  alert(res.error || 'Gagal membuat transaksi Midtrans');
                }
                document.getElementById('pay-midtrans').disabled = false;
              }).catch(function(e){ alert('Error: ' + e); document.getElementById('pay-midtrans').disabled = false; });
            });
          </script>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
