<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';

$code = trim($_REQUEST['code'] ?? '');
if (!$code) {
    echo json_encode(['ok' => false, 'error' => 'Kode kosong']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM produk WHERE sku = :code LIMIT 1');
$stmt->execute(['code' => $code]);
$product = $stmt->fetch();
if (!$product) {
    echo json_encode(['ok' => false, 'error' => 'Produk tidak ditemukan']);
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$key = (string)$product['id'];
if (isset($cart[$key])) {
    $cart[$key]['quantity'] += 1;
} else {
    $cart[$key] = [
        'id' => $product['id'],
        'nama' => $product['nama'],
        'harga' => $product['harga'],
        'quantity' => 1,
    ];
}
$_SESSION['cart'] = $cart;
$totalQty = array_reduce($cart, function($c, $i){ return $c + $i['quantity']; }, 0);

echo json_encode(['ok' => true, 'product' => ['id' => $product['id'], 'nama' => $product['nama']], 'cart_count' => $totalQty]);
