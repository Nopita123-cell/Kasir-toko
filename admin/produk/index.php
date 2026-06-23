<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_login();
if (($_SESSION['user']['role'] ?? '') !== 'Admin') {
  header('HTTP/1.1 403 Forbidden');
  exit('Forbidden');
}
$flash = flash_get('success');
$errors = [];

// Handle delete
if (isset($_GET['delete'])) {
  $delId = intval($_GET['delete']);
  $stmt = $pdo->prepare('SELECT image FROM produk WHERE id = :id LIMIT 1');
  $stmt->execute(['id' => $delId]);
  $row = $stmt->fetch();
  if ($row && !empty($row['image'])) {
    $p = __DIR__ . '/../../assets/images/' . basename($row['image']);
    if (is_file($p)) @unlink($p);
  }
  $pdo->prepare('DELETE FROM produk WHERE id = :id')->execute(['id' => $delId]);
  flash_set('success', 'Produk berhasil dihapus.');
  redirect('/admin/produk/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = trim($_POST['nama'] ?? '');
  $harga = floatval($_POST['harga'] ?? 0);
  $stok = intval($_POST['stok'] ?? 0);
  $kategori_id = intval($_POST['kategori_id'] ?? 0);
  $sku = trim($_POST['sku'] ?? '') ?: null;
  if ($nama === '') {
    $errors[] = 'Nama produk wajib diisi.';
  }
  $imageName = null;
  if (!empty($_FILES['image']['name'])) {
    $uploadsDir = __DIR__ . '/../../assets/images/';
    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
    $tmp = $_FILES['image']['tmp_name'];
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = uniqid('pimg_') . '.' . $ext;
    move_uploaded_file($tmp, $uploadsDir . $imageName);
  }
  if (empty($errors)) {
    $sql = 'INSERT INTO produk (nama, sku, kategori_id, harga, stok, image) VALUES (:nama, :sku, :kategori_id, :harga, :stok, :image)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nama' => $nama, 'sku' => $sku, 'kategori_id' => $kategori_id, 'harga' => $harga, 'stok' => $stok, 'image' => $imageName]);
    flash_set('success', 'Produk berhasil ditambahkan.');
    redirect('/admin/produk/index.php');
  }
}

$produk = $pdo->query('SELECT p.*, k.nama AS kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id ORDER BY p.id DESC')->fetchAll();
$kategori = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama ASC')->fetchAll();
include __DIR__ . '/../../includes/header.php';
?>
<div class="container py-5">
  <h1 class="display-6 mb-4">Kelola Produk</h1>
  <?php if ($flash): ?>
    <div class="alert alert-success"><?= safe($flash) ?></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= safe($error) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <div class="card p-4 mb-4">
    <form method="post" enctype="multipart/form-data" class="row gy-3">
      <div class="col-md-4">
        <label class="form-label">Nama Produk</label>
        <input name="nama" class="form-control text-input" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">SKU (opsional)</label>
        <input name="sku" class="form-control text-input">
      </div>
      <div class="col-md-2">
        <label class="form-label">Harga</label>
        <input name="harga" type="number" step="0.01" class="form-control text-input" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Stok</label>
        <input name="stok" type="number" class="form-control text-input" required>
      </div>
      <div class="col-md-4">
          <label class="form-label">Kategori</label>
          <select name="kategori_id" class="form-select text-input">
            <option value="0">Tanpa Kategori</option>
            <?php foreach ($kategori as $row): ?>
              <option value="<?= $row['id'] ?>"><?= safe($row['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Gambar (opsional)</label>
          <input name="image" type="file" accept="image/*" class="form-control">
        </div>
      <div class="col-12 text-end">
        <button class="btn btn-primary button-pill">Tambah Produk</button>
      </div>
    </form>
  </div>
  <table class="table table-dark table-striped align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Gambar</th>
        <th>SKU</th>
        <th>Nama</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Stok</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($produk as $item): ?>
        <tr>
          <td><?= $item['id'] ?></td>
          <td style="width:80px">
            <?php if (!empty($item['image'])): ?>
              <img src="/assets/images/<?= safe($item['image']) ?>" alt="" style="max-width:70px; height:auto; border-radius:6px;">
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td><?= safe($item['sku'] ?? '-') ?></td>
          <td><?= safe($item['nama']) ?></td>
          <td><?= safe($item['kategori'] ?? 'Umum') ?></td>
          <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
          <td><?= $item['stok'] ?></td>
          <td>
            <a href="/admin/produk/edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-light">Edit</a>
            <a href="/admin/produk/index.php?delete=<?= $item['id'] ?>" onclick="return confirm('Hapus produk ini?')" class="btn btn-sm btn-danger">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php';
