<?php
declare(strict_types=1);

require_once './GuestBook/GuestBook.php';

session_start();

// Membuat CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Konfigurasi database
$host    = 'localhost';
$db      = 'tugas7';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Koneksi database
try {
    $pdo = new PDO(
        $dsn,
        'root',
        'nadyashallom05',
        $options
    );
} catch (PDOException $e) {
    die(
        'Koneksi database gagal: ' .
        htmlspecialchars($e->getMessage())
    );
}

// Membuat object GuestBook
$guestBook = new GuestBook($pdo);

// Variabel pesan
$errors = [];
$successMessage = '';

// Variabel form
$nama = '';
$email = '';
$pesan = '';

// Memproses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Memeriksa CSRF token
    $postToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $postToken)) {
        die('Kesalahan Keamanan: Token CSRF tidak cocok.');
    }

    // Mengambil data dari form
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    // Validasi nama
    if (empty($nama)) {
        $errors[] = 'Nama tidak boleh kosong.';
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format alamat email tidak valid.';
    }

    // Validasi pesan
    if (mb_strlen($pesan) < 5) {
        $errors[] = 'Pesan harus terdiri dari minimal lima karakter.';
    }

    // Menyimpan pesan jika tidak ada error
    if (empty($errors)) {

        if ($guestBook->simpanPesan($nama, $email, $pesan)) {

            $successMessage =
                'Terima kasih, ' .
                $nama .
                '! Pesan Anda berhasil dikirim.';

            // Membuat CSRF token baru
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Mengosongkan form setelah berhasil
            $nama = '';
            $email = '';
            $pesan = '';

        } else {

            $errors[] =
                'Gagal menyimpan pesan, silakan coba lagi.';
        }
    }
}

// Mengambil semua pesan
$daftarPesan = $guestBook->ambilSemuaPesan();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Buku Tamu Digital Perpustakaan</title>

    <style>

        /* =========================
           RESET
           ========================= */

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            padding: 50px 20px;
            background-color: #f5f5f5;
            font-family: Arial, Helvetica, sans-serif;
            color: #212529;
        }


        /* =========================
           CONTAINER
           ========================= */

        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }


        /* =========================
           CARD
           ========================= */

        .card {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }


        .card-body {
            padding: 22px;
        }


        .card-header {
            padding: 14px 22px;
            font-weight: bold;
        }


        /* =========================
           WARNA UNHAS
           ========================= */

        .card-header-unhas {
            background-color: #a6192e;
            color: white;
        }


        /* =========================
           JUDUL
           ========================= */

        h1 {
            font-size: 24px;
            margin: 0;
        }


        h2 {
            font-size: 20px;
            margin: 0;
        }


        /* =========================
           FORM
           ========================= */

        .mb-3 {
            margin-bottom: 18px;
        }


        .form-label {
            display: block;
            margin-bottom: 7px;
            font-size: 16px;
            font-weight: 400;
        }


        .form-control {
            display: block;
            width: 100%;
            padding: 10px 12px;
            font-size: 16px;
            font-family: Arial, Helvetica, sans-serif;
            color: #212529;
            background-color: #ffffff;
            border: 1px solid #ced4da;
            border-radius: 6px;
            outline: none;
        }


        .form-control:focus {
            border-color: #a6192e;
            box-shadow: 0 0 0 3px rgba(166, 25, 46, 0.15);
        }


        textarea.form-control {
            min-height: 110px;
            resize: vertical;
        }


        /* =========================
           BUTTON
           ========================= */

        .btn {
            display: block;
            width: 100%;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-size: 17px;
            cursor: pointer;
        }


        .btn-unhas {
            background-color: #a6192e;
            color: white;
        }


        .btn-unhas:hover {
            background-color: #8d1527;
        }


        /* =========================
           ALERT
           ========================= */

        .alert {
            padding: 12px 15px;
            margin-bottom: 18px;
            border-radius: 6px;
            font-size: 15px;
        }


        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
        }


        .alert-success {
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
        }


        .alert-light {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            color: #6c757d;
        }


        .text-center {
            text-align: center;
        }


        .text-muted {
            color: #6c757d;
        }


        /* =========================
           DAFTAR PESAN
           ========================= */

        .mt-4 {
            margin-top: 25px;
        }


        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }


        table {
            width: 100%;
            border-collapse: collapse;
        }


        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
            vertical-align: middle;
        }


        th {
            background-color: #a6192e;
            color: white;
            font-weight: bold;
        }


        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }


        /* =========================
           RESPONSIVE
           ========================= */

        @media (max-width: 600px) {

            body {
                padding: 20px 10px;
            }

            .card-body {
                padding: 18px;
            }

            h1 {
                font-size: 20px;
            }

            th,
            td {
                font-size: 14px;
                padding: 8px;
            }

        }

    </style>

</head>


<body>

    <div class="container">


        <!-- =====================================
             FORM BUKU TAMU
             ===================================== -->

        <div class="card">


            <!-- Header -->
            <div class="card-header card-header-unhas">

                <h1>
                    Buku Tamu Digital Perpustakaan
                </h1>

            </div>


            <!-- Isi -->
            <div class="card-body">


                <!-- Pesan Error -->
                <?php if (!empty($errors)): ?>

                    <div class="alert alert-danger">

                        <ul style="margin: 0; padding-left: 20px;">

                            <?php foreach ($errors as $error): ?>

                                <li>
                                    <?= htmlspecialchars($error) ?>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>


                <!-- Pesan Berhasil -->
                <?php if (!empty($successMessage)): ?>

                    <div class="alert alert-success">

                        <?= htmlspecialchars($successMessage) ?>

                    </div>

                <?php endif; ?>


                <!-- Form -->
                <form
                    action="./guestbook.php"
                    method="POST"
                >


                    <!-- CSRF Token -->
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                    >


                    <!-- Nama -->
                    <div class="mb-3">

                        <label
                            for="nama"
                            class="form-label"
                        >
                            Nama
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="nama"
                            name="nama"
                            required
                            placeholder="Masukkan nama Anda"
                            value="<?= htmlspecialchars($nama) ?>"
                        >

                    </div>


                    <!-- Email -->
                    <div class="mb-3">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Alamat Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            required
                            placeholder="nama@email.com"
                            value="<?= htmlspecialchars($email) ?>"
                        >

                    </div>


                    <!-- Pesan -->
                    <div class="mb-3">

                        <label
                            for="pesan"
                            class="form-label"
                        >
                            Pesan
                        </label>

                        <textarea
                            class="form-control"
                            id="pesan"
                            name="pesan"
                            rows="3"
                            required
                            placeholder="Tulis pesan atau kesan Anda (minimal 5 karakter)"
                        ><?= htmlspecialchars($pesan) ?></textarea>

                    </div>


                    <!-- Tombol -->
                    <button
                        type="submit"
                        class="btn btn-unhas"
                    >
                        Kirim Pesan
                    </button>


                </form>

            </div>

        </div>


        <!-- =====================================
             DAFTAR PESAN
             ===================================== -->

        <?php if (!empty($daftarPesan)): ?>

            <div class="card mt-4">


                <!-- Header Daftar Pesan -->
                <div class="card-header card-header-unhas">

                    <h2>
                        Daftar Pesan
                        (<?= count($daftarPesan) ?>)
                    </h2>

                </div>


                <div class="card-body">

                    <div class="table-responsive">

                        <table>

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

                                        <td>
                                            <?= htmlspecialchars(
                                                (string) $p['nama']
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                (string) $p['email']
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                (string) $p['pesan']
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                (string) $p['tanggal_kirim']
                                            ) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


        <?php else: ?>

            <div class="alert alert-light mt-4 text-center">

                Belum ada pesan. Jadilah yang pertama menulis di buku tamu!

            </div>

        <?php endif; ?>


    </div>

</body>

</html>