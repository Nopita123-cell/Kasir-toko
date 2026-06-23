<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';
require_login();
$cart = $_SESSION['cart'] ?? [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $stmt = $pdo->prepare('SELECT * FROM produk WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch();
    if ($product) {
        $key = (string)$productId;
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'id' => $product['id'],
                'nama' => $product['nama'],
                'harga' => $product['harga'],
                'quantity' => $quantity,
            ];
        }
        $_SESSION['cart'] = $cart;
        flash_set('success', 'Produk ditambahkan ke keranjang.');
        redirect('/kasir/transaksi.php');
    }
}
if (isset($_GET['remove'])) {
    $removeId = (string)intval($_GET['remove']);
    unset($cart[$removeId]);
    $_SESSION['cart'] = $cart;
    flash_set('success', 'Item dihapus dari keranjang.');
    redirect('/kasir/transaksi.php');
}
$products = $pdo->query('SELECT p.*, k.nama AS kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id ORDER BY p.nama ASC')->fetchAll();
$total = array_reduce($cart, fn($carry, $item) => $carry + ($item['harga'] * $item['quantity']), 0);
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="row">
    <div class="col-lg-8">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6">Transaksi Kasir</h1>
        <a href="/kasir/kasir/payment.php" class="btn btn-primary button-pill">Lanjut ke Pembayaran</a>
      </div>
      <?php if ($msg = flash_get('success')): ?>
        <div class="alert alert-success"><?= safe($msg) ?></div>
      <?php endif; ?>
      <div class="card p-4 mb-4">
        <h5>Daftar Produk</h5>
        <div class="mb-3 position-relative">
          <input id="product-search" class="form-control search-input" placeholder="Cari produk... (tekan / untuk fokus)" autocomplete="off" />
          <div id="typeahead-list" class="list-group position-absolute" style="z-index:50; top:58px; left:0; right:0; display:none;"></div>
          <input id="barcode-input" class="form-control" style="position:absolute; left:-9999px;" placeholder="Scan barcode" />
          <div class="mt-2">
            <button id="openScannerBtn" type="button" class="btn btn-outline-secondary">Buka Scanner Kamera</button>
          </div>
        </div>
        <div class="row g-3 mt-3" id="products-grid">
          <?php foreach ($products as $product): ?>
            <div class="col-md-6 product-col">
              <div class="product-card card bg-dark text-white h-100 p-3" data-id="<?= $product['id'] ?>" data-name="<?= strtolower(safe($product['nama'])) ?>" tabindex="0">
                <h6><?= safe($product['nama']) ?></h6>
                <p class="mb-1 small text-muted"><?= safe($product['kategori'] ?? 'Umum') ?></p>
                <p class="mb-2">Rp <?= number_format($product['harga'],0,',','.') ?></p>
                <form method="post" class="add-form d-flex gap-2">
                  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                  <input type="number" name="quantity" min="1" value="1" class="form-control text-input qty-input" style="width:80px;">
                  <button type="submit" class="btn btn-light button-pill add-btn">Tambah</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card p-4">
        <h5>Keranjang</h5>
        <?php if (empty($cart)): ?>
          <p>Belum ada produk di keranjang.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush mb-3">
            <?php foreach ($cart as $item): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                <div>
                  <strong><?= safe($item['nama']) ?></strong><br>
                  <?= $item['quantity'] ?> x Rp <?= number_format($item['harga'],0,',','.') ?>
                </div>
                <a href="?remove=<?= $item['id'] ?>" class="btn btn-sm btn-outline-light">Hapus</a>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-2"><span>Total</span><strong>Rp <?= number_format($total,0,',','.') ?></strong></div>
          </div>
          <a href="/kasir/payment.php" class="btn btn-primary w-100 button-pill">Bayar Sekarang</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

<script>
// Product search and keyboard shortcuts
(function(){
  const search = document.getElementById('product-search');
  if (!search) return;
  const cols = Array.from(document.querySelectorAll('.product-col'));
  let visible = cols.slice();
  let idx = -1;

  function refreshVisible(){
    visible = cols.filter(c => c.style.display !== 'none');
    if (idx >= visible.length) idx = visible.length -1;
  }

  search.addEventListener('input', function(){
    const q = this.value.trim().toLowerCase();
    cols.forEach(c => {
      const name = c.querySelector('.product-card').dataset.name || '';
      c.style.display = name.indexOf(q) !== -1 ? '' : 'none';
    });
    idx = -1;
    refreshVisible();
  });

  // keyboard shortcuts: '/' to focus search, up/down to navigate, Enter to add
  document.addEventListener('keydown', function(e){
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA'){
      e.preventDefault(); search.focus(); return;
    }
    if (document.activeElement === search){
      if (e.key === 'ArrowDown' || e.key === 'j'){
        e.preventDefault(); idx = Math.min(idx+1, visible.length-1); updateFocus();
      } else if (e.key === 'ArrowUp' || e.key === 'k'){
        e.preventDefault(); idx = Math.max(idx-1, 0); updateFocus();
      } else if (e.key === 'Enter'){
        e.preventDefault(); if (visible[idx]){
          const btn = visible[idx].querySelector('.add-form .add-btn');
          const qty = visible[idx].querySelector('.qty-input');
          if (qty) qty.focus();
          if (btn) btn.click();
        }
      }
    }
  });

  function updateFocus(){
    cols.forEach(c => c.querySelector('.product-card').classList.remove('focused'));
    if (visible[idx]){
      const card = visible[idx].querySelector('.product-card');
      card.classList.add('focused');
      card.scrollIntoView({behavior:'smooth', block:'center'});
    }
  }

  // click on card to focus qty
  cols.forEach(c => {
    c.addEventListener('click', function(e){
      const q = this.querySelector('.qty-input');
      if (q) q.focus();
    });
  });

  // Typeahead: fetch suggestions
  const taList = document.getElementById('typeahead-list');
  let taTimer = null;
  search.addEventListener('keyup', function(e){
    const q = this.value.trim();
    if (q.length < 2){ taList.style.display='none'; return; }
    clearTimeout(taTimer);
    taTimer = setTimeout(()=>{
      fetch('/api/products.php?q=' + encodeURIComponent(q)).then(r=>r.json()).then(items=>{
        taList.innerHTML = '';
        if (!items || !items.length){ taList.style.display='none'; return; }
        items.forEach((it, i)=>{
          const el = document.createElement('button');
          el.type='button';
          el.className='list-group-item list-group-item-action';
          el.textContent = (i+1) + '. ' + it.nama + ' — Rp ' + Number(it.harga).toLocaleString();
          el.dataset.id = it.id;
          el.addEventListener('click', ()=>{ addByAjax(it.id,1); });
          taList.appendChild(el);
        });
        taList.style.display='block';
      });
    }, 200);
  });

  // hide typeahead on outside click
  document.addEventListener('click', function(e){ if (!e.target.closest('#typeahead-list') && e.target !== search) taList.style.display='none'; });

  // barcode input handling: focus hidden input on 'b'
  const barcodeInput = document.getElementById('barcode-input');
  document.addEventListener('keydown', function(e){ if (e.key === 'b' && document.activeElement.tagName !== 'INPUT') { e.preventDefault(); barcodeInput.focus(); barcodeInput.value=''; } });
  barcodeInput.addEventListener('keydown', function(e){ if (e.key === 'Enter') { const code = this.value.trim(); if (code) fetch('/api/products.php?q=' + encodeURIComponent(code)).then(r=>r.json()).then(items=>{ if (items && items.length){ addByAjax(items[0].id,1); } else alert('Produk barcode tidak ditemukan'); this.value=''; } ); } });

  // quick-add by number key (1..9)
  document.addEventListener('keydown', function(e){ if (!isNaN(parseInt(e.key)) && e.key !== '0' && document.activeElement.tagName !== 'INPUT'){
      const num = parseInt(e.key,10); if (visible[num-1]){ const btn = visible[num-1].querySelector('.add-form .add-btn'); if (btn) btn.click(); }
  }});

  // addByAjax helper
  function addByAjax(id, qty){
    fetch('/kasir/api_add.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'product_id='+encodeURIComponent(id)+'&quantity='+encodeURIComponent(qty) })
      .then(r=>r.json()).then(function(res){ if (res.ok) location.reload(); else alert(res.error || 'Gagal menambahkan'); });
  }
})();
</script>
<!-- Scanner modal and html5-qrcode integration -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Scanner Kamera</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="reader" style="width:100%;"></div>
        <div id="scanStatus" class="mt-2 small text-muted"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(() => {
  let html5QrCode = null;
  const openBtn = document.getElementById('openScannerBtn');
  const scannerModalEl = document.getElementById('scannerModal');
  const scanStatus = document.getElementById('scanStatus');

  function startScanner(modalInstance){
    scanStatus.textContent = 'Mencari kamera...';
    html5QrCode = new Html5Qrcode('reader');
    Html5Qrcode.getCameras().then(cameras => {
      if (cameras && cameras.length) {
        const cameraId = cameras[0].id;
        scanStatus.textContent = 'Memindai...';
        html5QrCode.start(
          cameraId,
          { fps: 10, qrbox: { width: 250, height: 250 } },
          (decodedText, decodedResult) => {
            // send to backend to add to cart
            fetch('api_add_by_code.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/x-www-form-urlencoded'},
              body: 'code=' + encodeURIComponent(decodedText)
            }).then(r => r.json()).then(res => {
              if (res.ok) {
                scanStatus.textContent = 'Produk ditambahkan: ' + (res.product.nama || res.product.id);
                setTimeout(() => { modalInstance.hide(); location.reload(); }, 700);
              } else {
                scanStatus.textContent = 'Produk tidak ditemukan: ' + decodedText;
              }
            }).catch(err => {
              console.error(err); scanStatus.textContent = 'Terjadi kesalahan saat menambahkan';
            });
            // stop after first scan
            html5QrCode.stop().then(() => html5QrCode.clear()).catch(()=>{});
          },
          (errorMessage) => {
            // ignore frame errors
          }
        ).catch(err => {
          console.error(err); scanStatus.textContent = 'Gagal memulai kamera: ' + err;
        });
      } else {
        scanStatus.textContent = 'Tidak ada kamera yang ditemukan';
      }
    }).catch(err => {
      console.error(err); scanStatus.textContent = 'Izin kamera ditolak atau tidak tersedia';
    });
  }

  if (openBtn && scannerModalEl) {
    openBtn.addEventListener('click', () => {
      const modal = new bootstrap.Modal(scannerModalEl);
      modal.show();
      // start scanner after modal visible
      setTimeout(() => startScanner(modal), 250);
      scannerModalEl.addEventListener('hidden.bs.modal', () => {
        if (html5QrCode) html5QrCode.stop().then(()=>html5QrCode.clear()).catch(()=>{});
      }, { once: true });
    });
  }
})();
</script>
<?php include __DIR__ . '/../includes/footer.php';
