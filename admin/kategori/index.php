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
// handle delete
if (isset($_GET['delete'])) {
  $delId = intval($_GET['delete']);
  $pdo->prepare('DELETE FROM kategori WHERE id = :id')->execute(['id' => $delId]);
  flash_set('success', 'Kategori dihapus.');
  redirect('/admin/kategori/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    if ($nama === '') {
        $errors[] = 'Nama kategori wajib diisi.';
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO kategori (nama) VALUES (:nama)');
        $stmt->execute(['nama' => $nama]);
        flash_set('success', 'Kategori berhasil dibuat.');
        redirect('/admin/kategori/index.php');
    }
}
$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama ASC')->fetchAll();
include __DIR__ . '/../../includes/header.php';
?>
<div class="container py-5">
  <h1 class="display-6 mb-4">Kelola Kategori</h1>
  <?php if ($flash): ?>
    <div class="alert alert-success"><?= safe($flash) ?></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= safe($error) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" class="card p-4 mb-4">
    <div class="mb-3">
      <label class="form-label">Nama Kategori</label>
      <input name="nama" class="form-control text-input" required>
    </div>
    <button class="btn btn-primary button-pill">Tambah Kategori</button>
  </form>
  <div class="card p-4">
    <h5>Daftar Kategori</h5>
    <ul class="list-group list-group-flush">
      <?php foreach ($kategori as $row): ?>
        <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">
          <span><?= safe($row['nama']) ?></span>
          <span>
            <a href="/admin/kategori/index.php?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus kategori ini?')" class="btn btn-sm btn-danger">Hapus</a>
          </span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php';
