<?php
require_once __DIR__ . '/config/koneksi.php';
session_start();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u LIMIT 1');
    $stmt->execute(['u' => $username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']];
        if ($user['role'] === 'Admin') {
          header('Location: admin/index.php');
        } else {
          header('Location: kasir/index.php');
        }
        exit;
    }
    $err = 'Login gagal: periksa username/password';
}
include 'includes/header.php';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <form method="post" class="card p-4">
        <h3 class="mb-3">Login</h3>
        <?php if ($err) : ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100 button-pill">Masuk</button>
      </form>
    </div>
  </div>
</div>
<?php include 'includes/footer.php';
