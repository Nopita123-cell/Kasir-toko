<?php
// Konfigurasi aplikasi
const BASE_URL = 'http://localhost/Kasir';
const MIDTRANS_SERVER_KEY = '';
const MIDTRANS_CLIENT_KEY = '';

function app_url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
