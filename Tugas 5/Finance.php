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

// 3. Pemrosesan HTTP POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    // Verifikasi CSRF Token
    $postToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postToken)) {
        die('Kesalahan Keamanan: Token CSRF tidak cocok.');
    }
 
    // Mengambil input mentah
    $type = trim($_POST['type'] ?? '');
    $amountInput = trim($_POST['amount'] ?? '');
 
    // Validasi jenis transaksi
    if (!in_array($type, ['deposit', 'withdrawal'], true)) {
        $errors[] = 'Jenis transaksi tidak valid.';
    }
 
    // Validasi jumlah transaksi sebagai angka desimal positif
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $amountInput) || (float) $amountInput <= 0) {
        $errors[] = 'Jumlah transaksi harus berupa angka desimal positif (contoh: 50000 atau 50000.50).';
    }
 
    // Jika tidak ada error input, proses transaksi lewat kelas Transaction
    if (empty($errors)) {
        $amount = (float) $amountInput;
        $id = uniqid('trx_', true);
        $transaction = new Transaction($id, $type, $amount);
 
        try {
            $newBalance = $transaction->process((float) $_SESSION['balance']);
 
            // Simpan saldo baru dan riwayat transaksi ke session
            $_SESSION['balance'] = $newBalance;
            $_SESSION['transactions'][] = $transaction->toArray();
 
            $label = match ($type) {
                'deposit'    => 'Deposit',
                'withdrawal' => 'Penarikan',
            };
 
            $successMessage = $label . ' sebesar Rp ' . number_format($amount, 2, ',', '.') . ' berhasil diproses.';
 
            // Regenerasi CSRF token setelah sukses untuk keamanan tambahan
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (RuntimeException | InvalidArgumentException $e) {
            // Saldo tidak cukup atau jenis transaksi tidak dikenali
            $errors[] = $e->getMessage();
        }
    }
}