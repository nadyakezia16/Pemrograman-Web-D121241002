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

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Keuangan Sederhana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body class="bg-light p-5">
 
    <div class="container" style="max-width: 600px;">
 
        <!-- KARTU SALDO -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <h2 class="h6 text-muted mb-1">Sisa Saldo Anda</h2>
                <p class="display-6 mb-0">Rp <?= htmlspecialchars(number_format((float) $_SESSION['balance'], 2, ',', '.')) ?></p>
            </div>
        </div>
 
        <!-- FORMULIR TRANSAKSI -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0">Formulir Transaksi Keuangan</h1>
            </div>
            <div class="card-body">
 
                <!-- Tampilan Pesan Error jika Validasi Gagal -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
 
                <!-- Tampilan Pesan Sukses -->
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>
 
                <form action="./finance.php" method="POST">
                    <!-- CSRF Token Hidden Input -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
 
                    <div class="mb-3">
                        <label for="type" class="form-label">Jenis Transaksi</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="" selected disabled>Pilih jenis transaksi</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Penarikan</option>
                        </select>
                    </div>
 
                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah (Rp)</label>
                        <input type="text" class="form-control" id="amount" name="amount" required placeholder="Contoh: 100000 atau 100000.50">
                    </div>
 
                    <button type="submit" class="btn btn-primary w-100">Proses Transaksi</button>
                </form>
 
            </div>
        </div>
 
        <!-- RIWAYAT TRANSAKSI -->
        <?php if (!empty($_SESSION['transactions'])): ?>
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h2 class="h5 mb-0">Riwayat Transaksi</h2>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach (array_reverse($_SESSION['transactions']) as $t): ?>
                            <?php
                                $typeLabel = match ($t['type']) {
                                    'deposit'    => 'Deposit',
                                    'withdrawal' => 'Penarikan',
                                    default      => 'Tidak diketahui',
                                };
                                $badgeClass = $t['type'] === 'deposit' ? 'text-bg-success' : 'text-bg-danger';
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <span class="badge <?= htmlspecialchars($badgeClass) ?> me-2"><?= htmlspecialchars($typeLabel) ?></span>
                                    <!-- Mencegah XSS saat merender data masukan -->
                                    <?= htmlspecialchars((string) $t['id']) ?>
                                </span>
                                <strong>Rp <?= htmlspecialchars(number_format((float) $t['amount'], 2, ',', '.')) ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
 
    </div>
 
</body>
</html>
 