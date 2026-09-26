<?php
declare(strict_types=1);
require_once './Transaction.php';
 
session_start();
 
// 1. Generate CSRF Token jika belum ada di session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
 
// 2. Inisialisasi saldo dan riwayat transaksi di session jika belum ada
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 0.0;
}
if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
}
 
$errors = [];
$successMessage = '';