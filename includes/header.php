<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Web Kasir</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/theme.css">
</head>
<?php if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); } ?>
<?php $theme = $_SESSION['theme'] ?? 'light'; ?>
<body class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<nav class="navbar navbar-dark bg-dark">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand" href="/">Web Kasir</a>
      <div>
      <a href="/Kasir/toggle_theme.php?return=<?php echo urlencode($_SERVER['REQUEST_URI'] ?? '/Kasir/'); ?>" class="btn btn-sm btn-outline-light button-pill me-2">Mode: <?= $theme === 'dark' ? 'Gelap' : 'Terang' ?></a>
      <?php if (!empty($_SESSION['user'])): ?>
        <?php if ($_SESSION['user']['role'] === 'Admin'): ?>
          <a class="btn btn-outline-light btn-sm button-pill me-2" href="/Kasir/admin/index.php">Admin</a>
        <?php else: ?>
          <a class="btn btn-outline-light btn-sm button-pill me-2" href="/Kasir/kasir/index.php">Kasir</a>
        <?php endif; ?>
        <a class="btn btn-outline-light btn-sm button-pill" href="/Kasir/logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-outline-light btn-sm button-pill" href="/Kasir/login.php">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
