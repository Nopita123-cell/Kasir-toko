<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$curr = $_SESSION['theme'] ?? 'light';
$_SESSION['theme'] = $curr === 'dark' ? 'light' : 'dark';
$return = $_GET['return'] ?? '/';
header('Location: ' . $return);
exit;
