<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_login();

header('Content-Type: application/json');
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo json_encode(['error' => 'Cart kosong']);
    exit;
}
$gross = array_reduce($cart, fn($s, $i) => $s + ($i['harga'] * $i['quantity']), 0);
$order_id = 'ORDER-' . time() . '-' . rand(1000,9999);
$items = [];
foreach ($cart as $i) {
    $items[] = [
        'id' => $i['id'],
        'price' => (int)$i['harga'],
        'quantity' => (int)$i['quantity'],
        'name' => $i['nama']
    ];
}

$payload = [
    'transaction_details' => ['order_id' => $order_id, 'gross_amount' => (int)$gross],
    'item_details' => $items,
    'customer_details' => ['first_name' => $_SESSION['user']['username'] ?? 'Kasir']
];

if (empty(MIDTRANS_SERVER_KEY)) {
    echo json_encode(['error' => 'MIDTRANS_SERVER_KEY belum diatur di config/config.php']);
    exit;
}

$url = 'https://app.sandbox.midtrans.com/snap/v1/transactions';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':' )
]);
$resp = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
if ($err) {
    echo json_encode(['error' => $err]);
    exit;
}

echo $resp;
