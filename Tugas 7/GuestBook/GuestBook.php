<?php
declare(strict_types=1);

class GuestBook
{
    public function __construct(private readonly PDO $pdo)
    {
    }
}
    /**
     * Menyimpan satu pesan baru ke tabel buku_tamu.
     * Memakai prepared statement agar aman dari SQL Injection.
     */
    public function simpanPesan(string $nama, string $email, string $pesan): bool
    {
        $sql = 'INSERT INTO buku_tamu (nama, email, pesan) VALUES (:nama, :email, :pesan)';
 
        $stmt = $this->pdo->prepare($sql);
 
        return $stmt->execute([
            ':nama'  => $nama,
            ':email' => $email,
            ':pesan' => $pesan,
        ]);
    }