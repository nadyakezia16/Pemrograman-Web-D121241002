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

## 3. Simulasi Normalisasi
 
### 3.1 Unnormalized Form (UNF)
 
Data mentah awal masih berupa satu tabel datar dengan atribut **multivalue** (satu transaksi bisa memuat beberapa buku sekaligus dalam satu sel).
 
| id_transaksi | nim | nama_mahasiswa | prodi | kode_buku | judul_buku | nama_penerbit | kota_penerbit | tanggal_pinjam | tanggal_kembali |
|---|---|---|---|---|---|---|---|---|---|
| TR001 | D121001 | Rian Saputra | Informatika | BK001, BK005 | Pemrograman Web, Basis Data Lanjut | Penerbit Informatika, Penerbit Andi | Bandung, Yogyakarta | 2026-09-01 | 2026-09-10 |
| TR002 | D121002 | Nurul Amalia | Sistem Informasi | BK001 | Pemrograman Web | Penerbit Informatika | Bandung | 2026-09-03 | 2026-09-12 |
 
**Masalah**: kolom `kode_buku`, `judul_buku`, `nama_penerbit`, dan `kota_penerbit` berisi lebih dari satu nilai dalam satu sel (bukan nilai atomik) → melanggar syarat 1NF.
 
---
 
### 3.2 First Normal Form (1NF)
 
Pecah atribut multivalue sehingga setiap sel hanya berisi satu nilai atomik (satu baris per buku yang dipinjam).
 
| id_transaksi | nim | nama_mahasiswa | prodi | kode_buku | judul_buku | nama_penerbit | kota_penerbit | tanggal_pinjam | tanggal_kembali |
|---|---|---|---|---|---|---|---|---|---|
| TR001 | D121001 | Rian Saputra | Informatika | BK001 | Pemrograman Web | Penerbit Informatika | Bandung | 2026-09-01 | 2026-09-10 |
| TR001 | D121001 | Rian Saputra | Informatika | BK005 | Basis Data Lanjut | Penerbit Andi | Yogyakarta | 2026-09-01 | 2026-09-10 |
| TR002 | D121002 | Nurul Amalia | Sistem Informasi | BK001 | Pemrograman Web | Penerbit Informatika | Bandung | 2026-09-03 | 2026-09-12 |
 
**Sudah 1NF**, tapi kunci komposit tabel ini adalah `(id_transaksi, kode_buku)`. Masalahnya: `nama_mahasiswa` dan `prodi` hanya bergantung pada `nim` (bagian dari kunci), bukan pada kunci komposit penuh → **partial dependency**, melanggar syarat 2NF.
 
---

### 3.3 Second Normal Form (2NF)
 
Hilangkan partial dependency dengan memecah tabel berdasarkan atribut mana yang benar-benar bergantung pada bagian kunci mana.
 
**Tabel Mahasiswa** (nama_mahasiswa, prodi bergantung penuh pada nim)
| nim | nama_mahasiswa | prodi |
|---|---|---|
| D121001 | Rian Saputra | Informatika |
| D121002 | Nurul Amalia | Sistem Informasi |
 
**Tabel Buku_Penerbit** (judul_buku, nama_penerbit, kota_penerbit bergantung penuh pada kode_buku)
| kode_buku | judul_buku | nama_penerbit | kota_penerbit |
|---|---|---|---|
| BK001 | Pemrograman Web | Penerbit Informatika | Bandung |
| BK005 | Basis Data Lanjut | Penerbit Andi | Yogyakarta |
 
**Tabel Peminjaman** (tanggal_pinjam, tanggal_kembali bergantung pada id_transaksi)
| id_transaksi | nim | tanggal_pinjam | tanggal_kembali |
|---|---|---|---|
| TR001 | D121001 | 2026-09-01 | 2026-09-10 |
| TR002 | D121002 | 2026-09-03 | 2026-09-12 |
 
**Tabel Detail_Peminjaman** (kunci komposit, penghubung Peminjaman ↔ Buku)
| id_transaksi | kode_buku |
|---|---|
| TR001 | BK001 |
| TR001 | BK005 |
| TR002 | BK001 |
 
**Sudah 2NF**, tapi tabel `Buku_Penerbit` masih punya masalah: `nama_penerbit` dan `kota_penerbit` sebenarnya bergantung pada identitas penerbit (bukan langsung pada `kode_buku`) → **transitive dependency** (`kode_buku` → `id_penerbit` → `nama_penerbit`, `kota_penerbit`), melanggar syarat 3NF.
 
---
 
 ### 3.4 Third Normal Form (3NF)
 
Hilangkan transitive dependency dengan memisahkan entitas Penerbit dari Buku.
 
**Tabel Penerbit**
| id_penerbit | nama_penerbit | kota_penerbit |
|---|---|---|
| PB01 | Penerbit Informatika | Bandung |
| PB02 | Penerbit Andi | Yogyakarta |
 
**Tabel Buku**
| kode_buku | judul_buku | id_penerbit |
|---|---|---|
| BK001 | Pemrograman Web | PB01 |
| BK005 | Basis Data Lanjut | PB02 |
 
Tabel `Mahasiswa`, `Peminjaman`, dan `Detail_Peminjaman` tetap seperti pada tahap 2NF karena tidak memiliki transitive dependency.
 
**Hasil akhir: seluruh tabel sudah memenuhi syarat 3NF** — setiap atribut non-kunci bergantung penuh dan hanya pada primary key tabelnya masing-masing, tidak ada partial dependency maupun transitive dependency.
 
---
 
 ## 4. Rancangan Tabel Akhir (3NF)
 
### Tabel `mahasiswa`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| nim | VARCHAR(10) | PRIMARY KEY |
| nama_mahasiswa | VARCHAR(100) | NOT NULL |
| prodi | VARCHAR(50) | NOT NULL |
| no_hp | VARCHAR(15) | NULL |
 
### Tabel `penerbit`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| id_penerbit | VARCHAR(5) | PRIMARY KEY |
| nama_penerbit | VARCHAR(100) | NOT NULL |
| kota_penerbit | VARCHAR(50) | NULL |
 
### Tabel `buku`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| kode_buku | VARCHAR(10) | PRIMARY KEY |
| judul_buku | VARCHAR(150) | NOT NULL |
| tahun_terbit | YEAR | NULL |
| stok | INT | NOT NULL DEFAULT 0 |
| id_penerbit | VARCHAR(5) | FOREIGN KEY → penerbit(id_penerbit) |
 
### Tabel `peminjaman`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| id_peminjaman | VARCHAR(10) | PRIMARY KEY |
| nim | VARCHAR(10) | FOREIGN KEY → mahasiswa(nim) |
| tanggal_pinjam | DATE | NOT NULL |
| tanggal_kembali | DATE | NULL |
| status | ENUM('dipinjam','dikembalikan','terlambat') | NOT NULL DEFAULT 'dipinjam' |
 
### Tabel `detail_peminjaman`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| id_peminjaman | VARCHAR(10) | PRIMARY KEY (komposit), FOREIGN KEY → peminjaman(id_peminjaman) |
| kode_buku | VARCHAR(10) | PRIMARY KEY (komposit), FOREIGN KEY → buku(kode_buku) |
 
---
