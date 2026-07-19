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
$editCategory = null;

if (isset($_GET['delete'])) {
  $delId = intval($_GET['delete']);
  $pdo->prepare('DELETE FROM kategori WHERE id = :id')->execute(['id' => $delId]);
  flash_set('success', 'Kategori dihapus.');
  redirect('/Kasir/admin/kategori/index.php');
}

if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $pdo->prepare('SELECT id, nama FROM kategori WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $editId]);
    $editCategory = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $nama = trim($_POST['nama'] ?? '');
    if ($nama === '') {
        $errors[] = 'Nama kategori wajib diisi.';
    }

    if (empty($errors)) {
        if ($action === 'update') {
            $editId = intval($_POST['id'] ?? 0);
            $exists = $pdo->prepare('SELECT id FROM kategori WHERE nama = :nama AND id != :id LIMIT 1');
            $exists->execute(['nama' => $nama, 'id' => $editId]);
            if ($exists->fetch()) {
                $errors[] = 'Nama kategori sudah ada.';
            }
            if (empty($errors)) {
                $stmt = $pdo->prepare('UPDATE kategori SET nama = :nama WHERE id = :id');
                $stmt->execute(['nama' => $nama, 'id' => $editId]);
                flash_set('success', 'Kategori berhasil diperbarui.');
                redirect('/Kasir/admin/kategori/index.php');
            }
        } else {
            $exists = $pdo->prepare('SELECT id FROM kategori WHERE nama = :nama LIMIT 1');
            $exists->execute(['nama' => $nama]);
            if ($exists->fetch()) {
                $errors[] = 'Nama kategori sudah ada.';
            }
            if (empty($errors)) {
                $stmt = $pdo->prepare('INSERT INTO kategori (nama) VALUES (:nama)');
                $stmt->execute(['nama' => $nama]);
                flash_set('success', 'Kategori berhasil dibuat.');
                redirect('/Kasir/admin/kategori/index.php');
            }
        }
    }
}
$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama ASC')->fetchAll();
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-shell">
  <?php include __DIR__ . '/../sidebar.php'; ?>
  <main class="admin-main">
    <div class="page-head">
      <div>
        <h1 class="page-title">Kelola Kategori</h1>
        <p class="text-muted mb-0">Atur pengelompokan produk agar kasir lebih cepat.</p>
      </div>
    </div>
    <?php if ($flash): ?>
      <div class="alert alert-success"><?= safe($flash) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= safe($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="card p-4 mb-4">
      <input type="hidden" name="action" value="<?= $editCategory ? 'update' : 'create' ?>">
      <?php if ($editCategory): ?>
        <input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>">
      <?php endif; ?>
      <div class="mb-3">
        <label class="form-label">Nama Kategori</label>
        <input name="nama" class="form-control text-input" value="<?= safe($editCategory['nama'] ?? '') ?>" required>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary button-pill"><?= $editCategory ? 'Simpan Perubahan' : 'Tambah Kategori' ?></button>
        <?php if ($editCategory): ?>
          <a href="/Kasir/admin/kategori/index.php" class="btn btn-outline-secondary button-pill">Batal</a>
        <?php endif; ?>
      </div>
    </form>
    <div class="card p-4">
      <h5 class="mb-3">Daftar Kategori</h5>
      <ul class="list-group list-group-flush">
        <?php foreach ($kategori as $row): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?= safe($row['nama']) ?></span>
            <span class="d-flex gap-2">
              <a href="/Kasir/admin/kategori/index.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
              <a href="/Kasir/admin/kategori/index.php?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus kategori ini?')" class="btn btn-sm btn-danger">Hapus</a>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php';
