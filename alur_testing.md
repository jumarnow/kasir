Viewed plan_monitoring_proses.md:334-362
Edited plan_monitoring_proses.md
Ran command: `php artisan make:seeder TestUserSeeder`
Edited TestUserSeeder.php
Ran command: `php artisan db:seed --class=TestUserSeeder`

Saya telah membuat dan menjalankan seeder untuk data user test. 

Berikut adalah **Akun Test** yang bisa Anda gunakan (password untuk semua akun adalah: `password123`):

1. **Admin**
   - Username: `admin_test` (atau Email: `admin@test.com`)
   - Role: Admin (Bisa semua akses + Track Out Final)
2. **Designer**
   - Username: `designer_test` (atau Email: `designer@test.com`)
   - Role: Designer (Hanya bisa Track In & Track Out desain)
3. **Operator Produksi**
   - Username: `operator_test` (atau Email: `operator@test.com`)
   - Role: Operator (Hanya bisa Track In & Track Out produksi)

---

### Alur Testing Manual (Sesuai Konsep Gambar)

Silakan ikuti langkah-langkah berikut untuk mensimulasikan alur kerjanya:

#### Tahap 1: Persiapan Orderan (Sebagai Admin)
1. Login menggunakan akun `admin_test`.
2. Masuk ke menu **Transaksi Baru** dan buat 1 orderan baru seperti biasa.
3. Setelah selesai tersimpan, lihat/cetak Invoice. Catat atau copy **Nomor Invoice**-nya (Misal: `INV-2026...`).
4. (Opsional) Cek menu **Monitoring Process > Status Order**, orderan tersebut seharusnya berstatus "Pending".
5. Logout dari akun Admin.

#### Tahap 2: Proses Desain (Sebagai Designer)
1. Login menggunakan akun `designer_test`. Perhatikan bahwa menu yang tampil di sidebar sangat terbatas demi keamanan.
2. Buka menu **Monitoring Process > Track In Order**.
3. Ketik (atau *scan*) Nomor Invoice tadi ke kolom input, lalu tekan **Cari**.
4. Akan muncul detail orderannya. Klik tombol **TRACK IN SEKARANG**.
   *(Sistem akan mencatat jam mulai kerja Designer, dan status orderan berubah menjadi "Desain")*
5. Jika pengerjaan desain sudah dianggap beres, buka menu **Track Out Order**.
6. Cari invoice tersebut lagi, lalu klik **TRACK OUT SEKARANG**.
7. Logout.

#### Tahap 3: Proses Produksi / Cetak (Sebagai Operator)
1. Login menggunakan akun `operator_test`.
2. Buka menu **Track In Order**, cari invoice tadi, lalu klik **TRACK IN SEKARANG**.
   *(Sistem akan mencatat jam mulai kerja Produksi, dan status orderan berubah menjadi "Produksi")*
3. Jika barang fisik sudah selesai diproduksi, buka menu **Track Out Order**.
4. Cari invoice tadi, lalu klik **TRACK OUT SEKARANG**.
   *(Status orderan kini otomatis berubah menjadi "Selesai" atau "Completed")*
5. Logout.

#### Tahap 4: Pengambilan Barang / Finalisasi (Sebagai Admin)
1. Login kembali menggunakan akun `admin_test`.
2. Buka menu **Monitoring Process > Status Order**. Anda akan melihat tabel monitoring sudah terisi lengkap dengan nama Designer, nama Operator, dan jam kerjanya. Statusnya saat ini "Selesai".
3. Saat barang akan diberikan ke Customer atau Kurir, buka menu **Track Out Order**.
4. Cari invoice tersebut. Sebagai admin, Anda akan melihat tambahan **opsi radio button** khusus admin (Diambil Customer / Diambil Kurir / Diantar).
5. Pilih salah satu (misalnya: *Diambil Customer*), lalu klik **TRACK OUT SEKARANG**.
6. Buka kembali halaman **Status Order**. Orderan sekarang statusnya sudah terekam di kolom status akhir beserta jam pengambilannya.

Silakan dicoba! Beri tahu saya jika ada bagian alur yang ingin diubah.