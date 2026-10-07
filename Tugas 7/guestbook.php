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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body class="bg-light p-5">

    <div class="container" style="max-width: 700px;">

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0">Buku Tamu Digital Perpustakaan</h1>
            </div>
            <div class="card-body">

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <form action="./guestbook.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama Anda">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="nama@email.com">
                    </div>

                    <div class="mb-3">
                        <label for="pesan" class="form-label">Pesan</label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="3" required placeholder="Tulis pesan atau kesan Anda (minimal 5 karakter)"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Kirim Pesan</button>
                </form>

            </div>
        </div>
        <?php if (!empty($daftarPesan)): ?>
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h2 class="h5 mb-0">Daftar Pesan (<?= count($daftarPesan) ?>)</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Pesan</th>
                            <th>Tanggal Kirim</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarPesan as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $p['nama']) ?></td>
                                <td><?= htmlspecialchars((string) $p['email']) ?></td>
                                <td><?= htmlspecialchars((string) $p['pesan']) ?></td>
                                <td><?= htmlspecialchars((string) $p['tanggal_kirim']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-light border mt-4 text-center text-muted">
        Belum ada pesan. Jadilah yang pertama menulis di buku tamu!
    </div>
<?php endif; ?>

    </div>

</body>
</html>