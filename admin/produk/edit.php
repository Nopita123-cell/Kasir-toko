<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_login();
if (($_SESSION['user']['role'] ?? '') !== 'Admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Forbidden');
}
$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('/admin/produk/index.php');
$stmt = $pdo->prepare('SELECT * FROM produk WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();
if (!$product) redirect('/admin/produk/index.php');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $harga = floatval($_POST['harga'] ?? 0);
    $stok = intval($_POST['stok'] ?? 0);
    $kategori_id = intval($_POST['kategori_id'] ?? 0);
    $sku = trim($_POST['sku'] ?? '') ?: null;
    if ($nama === '') $errors[] = 'Nama produk wajib diisi.';
    $imageName = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        $uploadsDir = __DIR__ . '/../../assets/images/';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
        $tmp = $_FILES['image']['tmp_name'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('pimg_') . '.' . $ext;
        move_uploaded_file($tmp, $uploadsDir . $imageName);
        // remove old
        if (!empty($product['image'])) {
            @unlink($uploadsDir . basename($product['image']));
        }
    }
    if (empty($errors)) {
      $pdo->prepare('UPDATE produk SET nama = :nama, sku = :sku, kategori_id = :kategori_id, harga = :harga, stok = :stok, image = :image WHERE id = :id')
        ->execute(['nama' => $nama, 'sku' => $sku, 'kategori_id' => $kategori_id, 'harga' => $harga, 'stok' => $stok, 'image' => $imageName, 'id' => $id]);
        flash_set('success', 'Produk berhasil diperbarui.');
        redirect('/admin/produk/index.php');
    }
}
$kategori = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama ASC')->fetchAll();
include __DIR__ . '/../../includes/header.php';
?>
<div class="container py-5">
  <h1 class="display-6 mb-4">Edit Produk</h1>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul><?php foreach ($errors as $e): ?><li><?= safe($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="card p-4">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nama</label>
        <input name="nama" value="<?= safe($product['nama']) ?>" class="form-control text-input">
      </div>
      <div class="col-md-6">
        <label class="form-label">SKU (opsional)</label>
        <input name="sku" value="<?= safe($product['sku'] ?? '') ?>" class="form-control text-input">
      </div>
      <div class="col-md-3">
        <label class="form-label">Harga</label>
        <input name="harga" type="number" step="0.01" value="<?= $product['harga'] ?>" class="form-control text-input">
      </div>
      <div class="col-md-3">
        <label class="form-label">Stok</label>
        <input name="stok" type="number" value="<?= $product['stok'] ?>" class="form-control text-input">
      </div>
      <div class="col-md-6">
        <label class="form-label">Kategori</label>
        <select name="kategori_id" class="form-select text-input">
          <option value="0">Tanpa Kategori</option>
          <?php foreach ($kategori as $row): ?>
            <option value="<?= $row['id'] ?>" <?= $row['id'] == $product['kategori_id'] ? 'selected' : '' ?>><?= safe($row['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Gambar (ganti jika perlu)</label>
        <input name="image" type="file" accept="image/*" class="form-control">
        <?php if (!empty($product['image'])): ?>
          <div class="mt-2"><img src="/assets/images/<?= safe($product['image']) ?>" style="max-width:150px"></div>
        <?php endif; ?>
      </div>
      <div class="col-12 text-end">
        <a href="/admin/produk/index.php" class="btn btn-outline-light button-pill">Batal</a>
        <button class="btn btn-primary button-pill">Simpan</button>
      </div>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php';
