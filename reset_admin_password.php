<?php
// One-off helper: reset admin password to 'admin123'.
// After running, delete this file for security.
require_once __DIR__ . '/config/koneksi.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = 'admin123';
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = :p WHERE username = :u');
    $stmt->execute(['p' => $hash, 'u' => 'admin']);
    echo '<h3>Password admin di-reset ke "admin123"</h3>';
    echo '<p>Hapus file <strong>reset_admin_password.php</strong> setelah verifikasi.</p>';
    exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Reset Admin Password</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;margin:40px;">
  <h2>Reset Admin Password</h2>
  <p>Tekan tombol untuk mereset password user <strong>admin</strong> menjadi <em>admin123</em>.</p>
  <form method="post">
    <button type="submit">Reset Password</button>
  </form>
  <p>Setelah berhasil, hapus file ini: <code>reset_admin_password.php</code></p>
</body>
</html>
