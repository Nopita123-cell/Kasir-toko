<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <h1 class="display-6">Kasir Dashboard</h1>
  <p>Halaman kasir sederhana. Mulai transaksi di <a href="transaksi.php">Transaksi</a>.</p>
</div>
<?php include __DIR__ . '/../includes/footer.php';
