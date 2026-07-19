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
$editUser = null;

if (isset($_GET['delete'])) {
  $delId = intval($_GET['delete']);
  if ($delId === ($_SESSION['user']['id'] ?? 0)) {
    flash_set('success', 'Tidak bisa menghapus user saat ini.');
    redirect('/Kasir/admin/user/index.php');
  }
  $pdo->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $delId]);
  flash_set('success', 'User dihapus.');
  redirect('/Kasir/admin/user/index.php');
}

if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $pdo->prepare('SELECT id, username, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $editId]);
    $editUser = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'Kasir');

    if ($username === '') {
        $errors[] = 'Username wajib diisi.';
    }

    if ($action === 'update') {
        $editId = intval($_POST['id'] ?? 0);
        $existing = $pdo->prepare('SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1');
        $existing->execute(['username' => $username, 'id' => $editId]);
        if ($existing->fetch()) {
            $errors[] = 'Username sudah digunakan oleh user lain.';
        }
        if (empty($errors)) {
            $sql = 'UPDATE users SET username = :username, role = :role';
            $params = ['username' => $username, 'role' => $role, 'id' => $editId];
            if ($password !== '') {
                $sql .= ', password = :password';
                $params['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            flash_set('success', 'User berhasil diperbarui.');
            redirect('/Kasir/admin/user/index.php');
        }
    } else {
        $existing = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $existing->execute(['username' => $username]);
        if ($existing->fetch()) {
            $errors[] = 'Username sudah digunakan.';
        }
        if ($password === '') {
            $errors[] = 'Password wajib diisi.';
        }
        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            $stmt->execute(['username' => $username, 'password' => $hash, 'role' => $role]);
            flash_set('success', 'User berhasil ditambahkan.');
            redirect('/Kasir/admin/user/index.php');
        }
    }
}
$users = $pdo->query('SELECT id, username, role, created_at FROM users ORDER BY id DESC')->fetchAll();
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-shell">
  <?php include __DIR__ . '/../sidebar.php'; ?>
  <main class="admin-main">
    <div class="page-head">
      <div>
        <h1 class="page-title">Kelola User</h1>
        <p class="text-muted mb-0">Atur akun admin dan kasir.</p>
      </div>
    </div>
    <?php if ($flash): ?>
      <div class="alert alert-success"><?= safe($flash) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= safe($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="card p-4 mb-4">
      <input type="hidden" name="action" value="<?= $editUser ? 'update' : 'create' ?>">
      <?php if ($editUser): ?>
        <input type="hidden" name="id" value="<?= (int)$editUser['id'] ?>">
      <?php endif; ?>
      <div class="row gy-3">
        <div class="col-md-4">
          <label class="form-label">Username</label>
          <input name="username" class="form-control text-input" value="<?= safe($editUser['username'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control text-input" placeholder="<?= $editUser ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan password' ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Role</label>
          <select name="role" class="form-select text-input">
            <option value="Admin" <?= (($editUser['role'] ?? 'Kasir') === 'Admin' ? 'selected' : '') ?>>Admin</option>
            <option value="Kasir" <?= (($editUser['role'] ?? 'Kasir') === 'Kasir' ? 'selected' : '') ?>>Kasir</option>
          </select>
        </div>
        <div class="col-12 text-end">
          <button class="btn btn-primary button-pill"><?= $editUser ? 'Simpan Perubahan' : 'Tambah User' ?></button>
          <?php if ($editUser): ?>
            <a href="/Kasir/admin/user/index.php" class="btn btn-outline-secondary button-pill">Batal</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
    <div class="card p-4">
      <h5 class="mb-3">Daftar User</h5>
      <div class="table-responsive">
        <table class="table align-middle">
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
                  <div class="d-flex gap-2">
                    <a href="/Kasir/admin/user/index.php?edit=<?= $user['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <?php if ($user['id'] != ($_SESSION['user']['id'] ?? 0)): ?>
                      <a href="/Kasir/admin/user/index.php?delete=<?= $user['id'] ?>" onclick="return confirm('Hapus user ini?')" class="btn btn-sm btn-danger">Hapus</a>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php';
