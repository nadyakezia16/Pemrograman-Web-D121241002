<?php
declare(strict_types=1);
require_once './GuestBook.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$host    = 'localhost';
$db      = 'akademik_db';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, 'root', '', $options);
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}

$guestBook = new GuestBook($pdo);

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postToken)) {
        die('Kesalahan Keamanan: Token CSRF tidak cocok.');
    }

    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    if (empty($nama)) {
        $errors[] = 'Nama tidak boleh kosong.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format alamat email tidak valid.';
    }

    if (mb_strlen($pesan) < 5) {
        $errors[] = 'Pesan harus terdiri dari minimal lima karakter.';
    }
}

if (empty($errors)) {
    if ($guestBook->simpanPesan($nama, $email, $pesan)) {
        $successMessage = 'Terima kasih, ' . $nama . '! Pesan Anda berhasil dikirim.';

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $errors[] = 'Gagal menyimpan pesan, silakan coba lagi.';
    }
}

$daftarPesan = $guestBook->ambilSemuaPesan();