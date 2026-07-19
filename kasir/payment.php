<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_login();
$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    header('Location: /Kasir/kasir/transaksi.php');
    exit;
}
$total = array_reduce($cart, fn($sum, $item) => $sum + ($item['harga'] * $item['quantity']), 0);
$cartCount = array_sum(array_column($cart, 'quantity'));
$error = '';
$cashPaid = 0.0;
$change = 0.0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = trim($_POST['payment_method'] ?? 'Tunai');
    $cashPaid = (float)($_POST['cash_paid'] ?? 0);
    $hasStockIssue = false;
    foreach ($cart as $item) {
        $stmt = $pdo->prepare('SELECT stok FROM produk WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $item['id']]);
        $stock = (int)$stmt->fetchColumn();
        if ($item['quantity'] > $stock) {
            $hasStockIssue = true;
            $error = 'Stok produk ' . $item['nama'] . ' tidak mencukupi untuk transaksi saat ini.';
            break;
        }
    }
    if ($hasStockIssue) {
        // no-op, keep the user on the payment page with the error shown
    } elseif ($payment_method === 'Tunai' && $cashPaid < $total) {
        $error = 'Uang tunai kurang untuk menyelesaikan transaksi.';
    } else {
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
        $paidAmount = $payment_method === 'Tunai' ? $cashPaid : $total;
        $change = max(0, $paidAmount - $total);
        $pdo->prepare('INSERT INTO payments (transaksi_id, amount, method, status, paid_at) VALUES (:tid, :amount, :method, :status, NOW())')->execute([
            'tid' => $transactionId,
            'amount' => $paidAmount,
            'method' => $payment_method,
            'status' => 'paid',
        ]);
        unset($_SESSION['cart']);
        $_SESSION['last_transaction_id'] = $transactionId;
        $_SESSION['last_payment_change'] = $change;
        redirect('/Kasir/kasir/receipt.php');
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card p-4">
        <h1 class="display-6">Pembayaran</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-muted">Item</span>
          <strong><?= number_format($cartCount) ?> produk</strong>
        </div>
        <div class="mb-3 small text-muted">Total yang harus dibayar:</div>
        <div class="fs-4 fw-semibold mb-4">Rp <?= number_format($total,0,',','.') ?></div>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= safe($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Metode Pembayaran</label>
            <select id="paymentMethod" name="payment_method" class="form-select text-input">
              <option selected>Tunai</option>
              <option>QRIS</option>
              <option>Transfer Bank</option>
              <option>E-wallet</option>
            </select>
          </div>
          <div id="cashField" class="mb-3">
            <label class="form-label">Jumlah Uang Tunai</label>
            <input type="number" name="cash_paid" min="0" step="100" class="form-control text-input" value="<?= number_format($cashPaid ?: $total, 0, '.', '') ?>">
          </div>
          <div class="mb-3 small text-muted">
            Kembalian akan dihitung otomatis jika metode pembayaran adalah Tunai.
          </div>
          <button class="btn btn-primary button-pill w-100">Bayar</button>
        </form>
        <div class="card bg-dark text-white p-3 mt-4">
          <div class="small text-muted mb-2">Ringkasan keranjang</div>
          <ul class="list-group list-group-flush">
            <?php foreach ($cart as $item): ?>
              <li class="list-group-item bg-transparent text-white px-0 border-0 d-flex justify-content-between">
                <span><?= safe($item['nama']) ?> x <?= (int)$item['quantity'] ?></span>
                <span>Rp <?= number_format($item['harga'] * $item['quantity'],0,',','.') ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
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
                    onSuccess: function(result){ location.href = '/Kasir/kasir/receipt.php'; },
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
        <script>
          (() => {
            const method = document.getElementById('paymentMethod');
            const cashField = document.getElementById('cashField');
            const syncCashVisibility = () => {
              if (!method || !cashField) return;
              cashField.style.display = method.value === 'Tunai' ? 'block' : 'none';
            };
            if (method) {
              method.addEventListener('change', syncCashVisibility);
              syncCashVisibility();
            }
          })();
        </script>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
