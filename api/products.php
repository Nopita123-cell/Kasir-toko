<?php
require_once __DIR__ . '/../config/koneksi.php';
header('Content-Type: application/json');
$q = trim($_GET['q'] ?? '');
$limit = intval($_GET['limit'] ?? 10);
if ($q === '') {
    echo json_encode([]);
    exit;
}
$param = '%' . strtolower($q) . '%';
$stmt = $pdo->prepare('SELECT id, nama, harga, sku FROM produk WHERE LOWER(nama) LIKE :q OR LOWER(sku) LIKE :q LIMIT :lim');
$stmt->bindValue(':q', $param, PDO::PARAM_STR);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();
echo json_encode($rows);
