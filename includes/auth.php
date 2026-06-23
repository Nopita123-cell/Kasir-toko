<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

function require_login()
{
    if (empty($_SESSION['user'])) {
        header('Location: /kasir/login.php');
        exit;
    }
}

function require_role($role)
{
    if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== $role) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Forbidden';
        exit;
    }
}
