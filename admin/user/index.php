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
  // prevent deleting self
  if ($delId === ($_SESSION['user']['id'] ?? 0)) {
    flash_set('success', 'Tidak bisa menghapus user saat ini.');
    redirect('/admin/user/index.php');
  }
  $pdo->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $delId]);
  flash_set('success', 'User dihapus.');
  redirect('/admin/user/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'Kasir');
    if ($username === '' || $password === '') {
        $errors[] = 'Username dan password wajib diisi.';
    }
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
        $stmt->execute(['username' => $username, 'password' => $hash, 'role' => $role]);
        flash_set('success', 'User berhasil ditambahkan.');
        redirect('/admin/user/index.php');
    }
}
$users = $pdo->query('SELECT id, username, role, created_at FROM users ORDER BY id DESC')->fetchAll();
include __DIR__ . '/../../includes/header.php';
?>
<div class="container py-5">
  <h1 class="display-6 mb-4">Kelola User</h1>
  <?php if ($flash): ?>
    <div class="alert alert-success"><?= safe($flash) ?></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= safe($error) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" class="card p-4 mb-4">
    <div class="row gy-3">
      <div class="col-md-4">
        <label class="form-label">Username</label>
        <input name="username" class="form-control text-input" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control text-input" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Role</label>
        <select name="role" class="form-select text-input">
          <option value="Admin">Admin</option>
          <option value="Kasir" selected>Kasir</option>
        </select>
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-primary button-pill">Tambah User</button>
      </div>
    </div>
  </form>
  <div class="card p-4">
    <h5>Daftar User</h5>
    <table class="table table-dark table-striped align-middle mt-3">
      <thead>
        <tr><th>ID</th><th>Username</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td><?= safe($user['username']) ?></td>
            <td><?= safe($user['role']) ?></td>
            <td><?= safe($user['created_at']) ?></td>
            <td>
              <?php if ($user['id'] != ($_SESSION['user']['id'] ?? 0)): ?>
                <a href="/admin/user/index.php?delete=<?= $user['id'] ?>" onclick="return confirm('Hapus user ini?')" class="btn btn-sm btn-danger">Hapus</a>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php';
