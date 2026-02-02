# Review Proyek Kasir untuk Support Percetakan

Berdasarkan review struktur proyek, berikut adalah analisis fitur yang **sudah ada** dan yang **belum ada** untuk mendukung kebutuhan percetakan:

---

## 📋 UPDATE INFORMASI DARI CUSTOMER (1 Feb 2026)

### Jenis Produk & Perhitungan Harga

Customer menjelaskan bahwa ada **2 tipe perhitungan harga**:

| Tipe Produk | Perhitungan | Contoh |
|-------------|-------------|--------|
| **Per Lembar/Biji** | Harga × Qty | Print A4 = Rp3.000/lembar, Print A3 = Rp6.000/lembar, Kartu Nama |
| **Per Dimensi (P×L)** | Harga × Panjang × Lebar | Banner, Spanduk, Stiker, Cutting Stiker |

**Contoh Bahan Banner/Spanduk:**
- Flexi 280 gram
- Flexi 440 gram

> ⚠️ **PENTING**: User harus bisa **menginput sendiri** produk dan memilih tipe perhitungan (per biji atau P×L)

### Revisi Cetak Dokumen

| No | Dokumen | Printer | Ukuran | Kegunaan |
|----|---------|---------|--------|----------|
| 1 | **SPK** | Thermal | - | Surat Perintah Kerja (internal produksi) |
| 2 | **Nota Penjualan** | Thermal | - | Penjualan Cash/Tunai |
| 3 | **Invoice** | Biasa | A5 | Pembayaran Tempo/Piutang |

---

## ✅ Fitur yang SUDAH ADA

### 1. Sistem User & Role Management ✅ UPDATED
| Fitur | Status | Detail |
|-------|--------|--------|
| Model User | ✅ Ada | `app/Models/User.php` |
| Model Role | ✅ Ada | `app/Models/Role.php` |
| Model Permission | ✅ Ada | `app/Models/Permission.php` |
| Relasi User-Role | ✅ Ada | Many-to-Many dengan pivot |
| Relasi Role-Permission | ✅ Ada | Many-to-Many dengan pivot |
| Cek hasRole() | ✅ Ada | Method di User model |
| Cek hasPermission() | ✅ Ada | Method di User model |

**Role yang sudah ada (5 role) ✅ SELESAI:**
| Role | Display Name | Deskripsi | Akses Omset |
|------|--------------|-----------|-------------|
| `kasir` | Kasir | Hanya input transaksi | ❌ Tidak |
| `admin` | Admin | Input, invoice, faktur, stok bahan | ❌ Tidak |
| `kepala_toko` | Kepala Toko | Seperti admin + edit pesanan | ❌ Tidak |
| `finance` | Finance | Akses penuh | ✅ Ya |
| `manager` | Manager | Akses penuh | ✅ Ya |

---

### 2. Sistem Transaksi
| Fitur | Status | Detail |
|-------|--------|--------|
| Model Transaction | ✅ Ada | `app/Models/Transaction.php` |
| Model TransactionItem | ✅ Ada | `app/Models/TransactionItem.php` |
| Invoice Number Generator | ✅ Ada | Format: `INV-YYYYMMDD-XXXX` |
| Relasi ke User (kasir) | ✅ Ada | `user_id` |
| Relasi ke Customer | ✅ Ada | `customer_id` |
| Subtotal, Diskon, Total | ✅ Ada | Field di transaction |
| Profit (laba) per item | ✅ Ada | Otomatis dari `price - cost_price` |
| Payment Method | ✅ Ada | Field payment_method |
| Status Transaksi | ✅ Ada | Field status (generic) |
| Notes | ✅ Ada | Field notes |

> ⚠️ **PERLU MODIFIKASI**: Status belum sesuai kebutuhan percetakan (pending, produksi, selesai belum bayar, DP, lunas)

---

### 3. Produk & Stok
| Fitur | Status | Detail |
|-------|--------|--------|
| Model Product | ✅ Ada | `app/Models/Product.php` |
| Kategori Produk | ✅ Ada | `app/Models/Category.php` |
| HPP (cost_price) | ✅ Ada | Field `cost_price` untuk modal |
| Multi Price Tier | ✅ Ada | `price`, `price_2`, `price_3` |
| Stock Tracking | ✅ Ada | Field `stock` dan `stock_alert` |
| Barcode & SKU | ✅ Ada | Field barcode dan sku |
| Stock Increment/Decrement | ✅ Ada | Method di model |

> ⚠️ **PERLU MODIFIKASI**: Tambah field `pricing_type` (per_unit / per_dimension) dan `cost_per_unit` untuk perhitungan P×L

---

### 4. Pelanggan
| Fitur | Status | Detail |
|-------|--------|--------|
| Model Customer | ✅ Ada | `app/Models/Customer.php` |
| Data Lengkap | ✅ Ada | Nama, email, phone, alamat |
| Price Tier | ✅ Ada | Harga berbeda per tier customer |
| Relasi ke Transaction | ✅ Ada | History pembelian |

---

### 5. Laporan
| Fitur | Status | Detail |
|-------|--------|--------|
| Laporan Penjualan | ✅ Ada | `reports/sales.blade.php` |
| Laporan Profit/Laba | ✅ Ada | `reports/profit.blade.php` |
| Agregasi per Hari/Minggu/Bulan | ✅ Ada | `ReportService.php` |
| Filter by User (kasir) | ✅ Ada | Parameter userId |

> ⚠️ **BELUM LENGKAP**: Belum ada laporan stok, piutang, pembukuan

---

### 6. Cetak Dokumen
| Fitur | Status | Detail |
|-------|--------|--------|
| Invoice (A4) | ✅ Ada | `transactions/invoice.blade.php` |
| Shipping Label | ✅ Ada | `transactions/shipping_label.blade.php` |

---

## ❌ Fitur yang BELUM ADA (Perlu Dikembangkan)

### A. Perhitungan & Transaksi

| Fitur | Status | Keterangan |
|-------|--------|------------|
| **Tipe Harga Produk** | ✅ Selesai | Field `pricing_type`: `per_unit` atau `per_dimension` |
| **Perhitungan P×L×Qty** | ✅ Selesai | `Product::calculatePrice()` dengan dimensi |
| **Perhitungan Per Biji** | ✅ Selesai | Harga × qty dengan price tier support |
| Status Pesanan Percetakan | ✅ Selesai | pending → production → completed → delivered |
| Upload File Desain | ✅ Selesai | Model `OrderFile` type: design |
| Upload File Cetak | ✅ Selesai | Model `OrderFile` type: print_ready |
| Tracking DP (Down Payment) | ✅ Selesai | `dp_amount`, `remaining_amount` |
| Piutang/Hutang | ✅ Selesai | `payment_status`, `due_date`, `hasDebt()` |

---

### B. Master Data Percetakan

| Fitur | Status | Keterangan |
|-------|--------|------------|
| Bahan/Material | ✅ Selesai | 9 data: Flexi 280gr, 440gr, Vinyl, Kertas HVS, dll |
| Finishing | ✅ Selesai | 8 data: Laminasi, Cutting, Mounting, dll |
| Display/Inventaris | ✅ Selesai | 7 data: Neon Box, Standing Banner, X Banner, dll |
| Stok Bahan (keluar-masuk) | ❌ Belum | Tracking penggunaan bahan |

> **💡 Konsep Produk vs Material:**
> - **Product** (Menu Jualan): Apa yang dipilih kasir & masuk invoice. Contoh: _"Cetak Banner 280gr"_ (Jasa + Bahan).
> - **Material** (Stok Gudang): Bahan baku fisik yang dibeli. Contoh: _"Roll Flexi 280gr"_.
> - **Hubungan**: Menjual **Product** idealnya akan mengurangi stok **Material**. Untuk saat ini (Fase 2), keduanya berdiri masing-masing (diketik manual), link otomatis akan dikerjakan di fase polishing jika diminta.

---

### C. Cetak Dokumen (REVISI)

| No | Dokumen | Printer | Ukuran | Status | Keterangan |
|----|---------|---------|--------|--------|------------|
| 1 | **SPK** | Thermal | - | ❌ Belum | Surat Perintah Kerja untuk produksi internal |
| 2 | **Nota Penjualan** | Thermal | - | ❌ Belum | Untuk penjualan cash/tunai |
| 3 | **Invoice** | Biasa | A5 | ⚠️ Modifikasi | Untuk pembayaran tempo/piutang (ubah dari A4 ke A5) |

---

### D. Laporan

| Fitur | Status | Keterangan |
|-------|--------|------------|
| Laporan Stok Bahan | ❌ Belum | Stok masuk-keluar bahan |
| Laporan Pembukuan | ❌ Belum | Kas masuk-keluar |
| Laporan Piutang | ❌ Belum | Daftar hutang customer |
| Laporan Omset | ⚠️ Perlu modifikasi | Sudah ada tapi perlu filter |
| Export PDF | ❌ Belum | Ekspor laporan ke PDF |
| Export Excel | ❌ Belum | Ekspor laporan ke Excel |

---

### E. QR Code

| Fitur | Status | Keterangan |
|-------|--------|------------|
| QR Code Transaksi | ❌ Belum | Generate QR per pesanan |
| QR Code Bahan | ❌ Belum | Generate QR per bahan |
| QR Code Display | ❌ Belum | Generate QR per display |

---

### F. Level Login & Hak Akses

| Role | Status | Keterangan |
|------|--------|------------|
| Kasir | ✅ Selesai | Input saja, tidak bisa lihat omset |
| Admin | ✅ Selesai | Input, invoice belum lunas, faktur, stok (tanpa omset) |
| Kepala Toko | ✅ Selesai | Sama seperti admin + edit pesanan |
| Finance | ✅ Selesai | Akses penuh |
| Manager | ✅ Selesai | Akses penuh |

---

### G. UI/UX

| Fitur | Status | Keterangan |
|-------|--------|------------|
| Dark Mode | ❌ Belum | Toggle tema gelap |
| Light Mode | ✅ Ada | Default theme |
| Responsive/Multiplatform | ⚠️ Perlu cek | Perlu review layout |

---

## 📊 Ringkasan Persentase Kesiapan

```
┌─────────────────────────────────┬───────────┐
│ Modul                           │ Kesiapan  │
├─────────────────────────────────┼───────────┤
│ User & Auth                     │ 70%       │
│ Role & Permission               │ 100% ✅   │
│ Transaksi Dasar                 │ 80% ✅    │
│ Produk & Kategori               │ 90% ✅    │
│ Customer                        │ 80%       │
│ Stok Produk                     │ 70%       │
│ Cetak Invoice                   │ 40%       │
│ Laporan                         │ 40%       │
├─────────────────────────────────┼───────────┤
│ Fitur Khusus Percetakan         │ 70% ✅    │
│ (Material, Finishing, Pricing)  │           │
├─────────────────────────────────┼───────────┤
│ TOTAL ESTIMASI                  │ ~70%      │
└─────────────────────────────────┴───────────┘
```

---

## 🎯 Rekomendasi Prioritas Pengerjaan (UPDATED)

### Fase 1: Fondasi ✅ SELESAI
1. ✅ Update Role sesuai kebutuhan (5 level) - **SELESAI**
2. ✅ Update Permission matrix - **SELESAI**
3. ✅ Buat model `Material` (bahan) - **SELESAI** (9 data)
4. ✅ Buat model `Finishing` - **SELESAI** (8 data)
5. ✅ Buat model `Display` - **SELESAI** (7 data)

### Fase 2: Core Produk & Transaksi Percetakan ✅ SELESAI
1. ✅ Modifikasi `Product` untuk support 2 tipe harga - **SELESAI**
   - `pricing_type` = `per_unit` | `per_dimension`
   - `calculatePrice()` method
2. ✅ Modifikasi `TransactionItem` - **SELESAI** (+width, +length, +area)
3. ✅ Modifikasi `Transaction` - **SELESAI** (order_status, payment_status, DP)
4. ✅ Buat model `OrderFile` - **SELESAI**
5. ✅ Sistem DP dan tracking piutang - **SELESAI**

### Fase 3: Dokumen & Cetak (REVISI)
1. ➕ **SPK (Thermal)** - Surat Perintah Kerja internal
2. ➕ **Nota Penjualan (Thermal)** - Untuk cash
3. ✏️ **Invoice (A5)** - Modifikasi dari A4 ke A5 untuk tempo

### Fase 4: Laporan & Export
1. ➕ Laporan Stok Bahan
2. ➕ Laporan Piutang
3. ➕ Laporan Pembukuan
4. ➕ Export PDF & Excel

### Fase 5: Polish
1. ➕ Implementasi QR Code
2. ➕ Dark Mode
3. ✏️ Review responsive design

---

## 📁 File Struktur yang Perlu Dibuat

```
app/Models/
├── Material.php          [DONE] ✅
├── Finishing.php         [DONE] ✅
├── Display.php           [DONE] ✅
├── OrderFile.php         [DONE] ✅
├── CashFlow.php          [NEW] (kas masuk/keluar)
├── Product.php           [DONE] ✅ + pricing_type, calculatePrice()
├── TransactionItem.php   [DONE] ✅ + width, length, area
└── Transaction.php       [DONE] ✅ + order_status, payment_status, DP

database/migrations/
├── create_materials_table.php      [DONE] ✅
├── create_finishings_table.php     [DONE] ✅
├── create_displays_table.php       [DONE] ✅
├── create_order_files_table.php    [DONE] ✅
├── create_cash_flows_table.php     [NEW]
├── add_pricing_fields_to_products  [DONE] ✅
├── add_dimension_fields_to_items   [DONE] ✅
└── add_printing_status_to_trans    [DONE] ✅

resources/views/
├── materials/            [NEW]
├── finishings/           [NEW]
├── displays/             [NEW]
├── transactions/
│   ├── spk_thermal.blade.php       [NEW] - SPK thermal
│   ├── receipt_thermal.blade.php   [NEW] - Nota cash thermal
│   └── invoice_a5.blade.php        [NEW] - Invoice A5 tempo
└── reports/
    ├── stock.blade.php             [NEW]
    ├── receivables.blade.php       [NEW]
    └── cashflow.blade.php          [NEW]
```

---

## 📝 Struktur Database Produk (PROPOSAL)

```sql
-- Modifikasi tabel products
ALTER TABLE products ADD COLUMN pricing_type ENUM('per_unit', 'per_dimension') DEFAULT 'per_unit';
ALTER TABLE products ADD COLUMN price_per_meter DECIMAL(12,2) NULL; -- harga per m² untuk P×L
ALTER TABLE products ADD COLUMN min_width DECIMAL(8,2) NULL; -- lebar minimum (cm)
ALTER TABLE products ADD COLUMN min_length DECIMAL(8,2) NULL; -- panjang minimum (cm)

-- Modifikasi tabel transaction_items
ALTER TABLE transaction_items ADD COLUMN width DECIMAL(8,2) NULL; -- lebar (cm)
ALTER TABLE transaction_items ADD COLUMN length DECIMAL(8,2) NULL; -- panjang (cm)
ALTER TABLE transaction_items ADD COLUMN area DECIMAL(12,4) NULL; -- luas (m²)
```

**Contoh Perhitungan (VERIFIED ✅):**
- **Per Unit**: Print A4, Qty=100, Harga=3000 → Total = 100 × 3000 = **Rp300.000** ✅
- **Per Dimensi**: Banner 200cm × 300cm, Harga=50.000/m² → Total = 6m² × 50.000 = **Rp300.000** ✅

---

Lanjut ke Fase 3: Dokumen & Cetak (SPK Thermal, Nota Thermal, Invoice A5)
