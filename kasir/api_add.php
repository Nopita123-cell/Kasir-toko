<?php
require_once __DIR__ . '/../includes/auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_login();
require_once __DIR__ . '/../config/koneksi.php';
header('Content-Type: application/json');
$productId = intval($_POST['product_id'] ?? 0);
$quantity = max(1, intval($_POST['quantity'] ?? 1));
if (!$productId) {
    echo json_encode(['error' => 'product_id required']);
    exit;
}
$stmt = $pdo->prepare('SELECT id, nama, harga, stok FROM produk WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();
if (!$product) {
    echo json_encode(['error' => 'product not found']);
    exit;
}
$cart = $_SESSION['cart'] ?? [];
$key = (string)$productId;
if (isset($cart[$key])) {
    $cart[$key]['quantity'] += $quantity;
} else {
    $cart[$key] = ['id' => $product['id'], 'nama' => $product['nama'], 'harga' => $product['harga'], 'quantity' => $quantity];
}
$_SESSION['cart'] = $cart;
$total = array_reduce($cart, fn($c, $i) => $c + ($i['harga'] * $i['quantity']), 0);
echo json_encode(['ok' => true, 'total' => $total, 'count' => count($cart)]);
