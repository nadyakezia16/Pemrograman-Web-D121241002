# Perancangan Database E-Library Kampus


## Identitas Mahasiswa

Nama : Nadya Kezia Goergina Shallom Toban
NIM  : D121241002

## 1. Deskripsi Skenario
 
Sistem ini merancang basis data relasional untuk peminjaman buku perpustakaan kampus. Sistem mencatat data **Mahasiswa**, **Buku**, **Penerbit**, serta riwayat **Peminjaman** dan pengembalian buku.
 
---
## 2. Identifikasi Entitas dan Atribut
 
### 2.1 Entitas Mahasiswa
| Atribut | Keterangan |
|---|---|
| **nim** | Primary Key |
| nama_mahasiswa | Atribut biasa |
| prodi | Atribut biasa |
| no_hp | Atribut biasa |
 
### 2.2 Entitas Penerbit
| Atribut | Keterangan |
|---|---|
| **id_penerbit** | Primary Key |
| nama_penerbit | Atribut biasa |
| kota_penerbit | Atribut biasa |
 
### 2.3 Entitas Buku
| Atribut | Keterangan |
|---|---|
| **kode_buku** | Primary Key |
| judul_buku | Atribut biasa |
| tahun_terbit | Atribut biasa |
| stok | Atribut biasa |
| id_penerbit | Foreign Key → Penerbit |
 
### 2.4 Entitas Peminjaman
| Atribut | Keterangan |
|---|---|
| **id_peminjaman** | Primary Key |
| nim | Foreign Key → Mahasiswa |
| tanggal_pinjam | Atribut biasa |
| tanggal_kembali | Atribut biasa |
| status | Atribut biasa |
 
### 2.5 Entitas Detail Peminjaman (penghubung Peminjaman ↔ Buku)
Karena satu transaksi peminjaman bisa mencakup lebih dari satu buku, relasi Peminjaman–Buku bersifat many-to-many sehingga dibutuhkan tabel penghubung.
 
| Atribut | Keterangan |
|---|---|
| **id_peminjaman** | Primary Key (komposit), Foreign Key → Peminjaman |
| **kode_buku** | Primary Key (komposit), Foreign Key → Buku |
 
---
