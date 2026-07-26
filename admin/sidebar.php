<?php
$currentPath = basename($_SERVER['PHP_SELF'] ?? 'index.php');
function admin_nav_item(string $href, string $label, string $icon, string $currentPath): string
{
    $active = basename($href) === $currentPath ? 'active' : '';
    return '<a class="nav-link ' . $active . '" href="' . $href . '"><i class="fa-solid ' . $icon . '"></i><span>' . $label . '</span></a>';
}
?>
<aside class="admin-sidebar">
  <div class="brand">
    <div class="brand-badge">W</div>
    <div>
      <h5>Web Kasir Nopita</h5>
      <p>Admin Panel</p>
    </div>
  </div>
  <nav class="sidebar-nav">
    <?= admin_nav_item('/Kasir/admin/index.php', 'Dashboard', 'fa-house', $currentPath) ?>
    <?= admin_nav_item('/Kasir/admin/produk/index.php', 'Produk', 'fa-box-open', $currentPath) ?>
    <?= admin_nav_item('/Kasir/admin/kategori/index.php', 'Kategori', 'fa-tags', $currentPath) ?>
    <?= admin_nav_item('/Kasir/admin/laporan/index.php', 'Laporan', 'fa-chart-line', $currentPath) ?>
    <?= admin_nav_item('/Kasir/admin/user/index.php', 'User', 'fa-users', $currentPath) ?>
    <a class="nav-link" href="/Kasir/logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
  </nav>
</aside>