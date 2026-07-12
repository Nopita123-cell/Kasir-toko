<?php
include __DIR__ . '/includes/header.php';
?>
<div class="hero-band position-relative overflow-hidden">
  <div class="container py-5">
    <div class="row align-items-center g-5">
      <div class="col-lg-7 text-lg-start text-center">
        <div class="badge rounded-pill bg-light text-dark px-3 py-2 mb-3">Point of Sale • Modern • Fast</div>
        <h1 class="display-font display-xxl mb-3" style="font-size:clamp(48px, 7vw, 92px); line-height:0.95;">Web Kasir Modern</h1>
        <p class="lead mb-4" style="max-width:640px;">Sistem kasir ringan, cepat, dan elegant untuk mengelola produk, transaksi, dan pembayaran tanpa ribet.</p>
        <div class="d-flex gap-3 flex-wrap justify-content-lg-start justify-content-center">
          <a href="/Kasir/login.php" class="hero-cta btn btn-light btn-lg">Mulai Sekarang</a>
          <a href="/Kasir/login.php" class="btn btn-outline-light btn-lg button-pill">Masuk ke Dashboard</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card p-4 shadow-lg border-0" style="background:rgba(255,255,255,0.08); backdrop-filter:blur(12px);">
          <h5 class="mb-3">Fitur utama</h5>
          <ul class="list-group list-group-flush">
            <li class="list-group-item bg-transparent border-0 text-white-50 px-0">• Produk & kategori terkelola</li>
            <li class="list-group-item bg-transparent border-0 text-white-50 px-0">• Transaksi kasir cepat dan aman</li>
            <li class="list-group-item bg-transparent border-0 text-white-50 px-0">• Integrasi pembayaran Midtrans</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container py-5">
  <div class="row g-4">
    <div class="col-md-4 mb-4">
      <a href="/Kasir/login.php" class="text-decoration-none text-reset">
        <div class="plan-card h-100">
          <h2>Admin</h2>
          <p>Kelola produk, kategori, user, laporan, dan integrasi pembayaran.</p>
          <span class="btn btn-outline-light btn-sm mt-3">Buka Panel</span>
        </div>
      </a>
    </div>
    <div class="col-md-4 mb-4">
      <a href="/Kasir/login.php" class="text-decoration-none text-reset">
        <div class="plan-card featured h-100">
          <h2>Kasir</h2>
          <p>Transaksi cepat, keranjang, pembayaran, dan cetak struk.</p>
          <span class="btn btn-light btn-sm mt-3 text-dark">Buka Panel</span>
        </div>
      </a>
    </div>
    <div class="col-md-4 mb-4">
      <a href="/Kasir/login.php" class="text-decoration-none text-reset">
        <div class="plan-card h-100">
          <h2>Integrasi</h2>
          <p>Midtrans sandbox support untuk pembayaran digital.</p>
          <span class="btn btn-outline-light btn-sm mt-3">Buka Panel</span>
        </div>
      </a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php';
