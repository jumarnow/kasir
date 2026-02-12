# Dashboard Filter Update

## Ringkasan Perubahan

Dashboard telah diupdate untuk menampilkan data transaksi yang berbeda berdasarkan role user:

### Role yang Terpengaruh
- **Admin** (`admin`)
- **Kasir** (`kasir`)

### Fitur yang Difilter

Untuk user dengan role `admin` atau `kasir`, dashboard akan menampilkan:

1. **Jumlah Transaksi Hari Ini**
   - Hanya menghitung transaksi yang dibuat oleh user tersebut (`transaction.user_id = auth()->id()`)
   - Menampilkan persentase perubahan dibandingkan dengan hari kemarin
   - Indikator visual:
     - 🟢 Hijau (↑) untuk peningkatan
     - 🔴 Merah (↓) untuk penurunan
     - ⚪ Abu-abu (—) untuk tidak ada perubahan

2. **Rata-rata Transaksi**
   - Dihitung dari total penjualan dibagi jumlah transaksi user tersebut
   - Formula: `Total Sales / Total Transactions` (keduanya sudah difilter)
   - Menampilkan nominal rata-rata per transaksi

3. **Grafik Penjualan 7 Hari Terakhir**
   - Hanya menampilkan transaksi user tersebut

4. **Produk Terlaris (30 Hari)**
   - Hanya produk dari transaksi user tersebut

5. **Profit Hari Ini**
   - Dihitung dari transaksi user tersebut

### Role Lainnya
User dengan role selain `admin` dan `kasir` (misalnya `super_admin` atau role khusus lainnya) akan melihat **semua transaksi** di dashboard tanpa filter.

## File yang Dimodifikasi

### 1. `/app/Services/DashboardService.php`
- Menambahkan method `shouldFilterByUser()` untuk mengecek apakah user perlu difilter
- Menambahkan method `applyUserFilter()` untuk menerapkan filter user_id
- Mengupdate semua method untuk menggunakan filter:
  - `salesLastSevenDays()` - Filter grafik penjualan
  - `todaySummary()` - Filter transaksi hari ini dan kemarin
  - `topProducts()` - Filter produk terlaris

### 2. `/resources/views/dashboard/index.blade.php`
- Menambahkan tampilan persentase perubahan transaksi
- Menambahkan indikator visual (icon arrow up/down)
- Menampilkan jumlah transaksi kemarin sebagai pembanding

## Cara Kerja

```php
// Cek apakah user memiliki role admin atau kasir
private function shouldFilterByUser(): bool
{
    $user = auth()->user();
    return $user && ($user->hasRole('admin') || $user->hasRole('kasir'));
}

// Terapkan filter jika diperlukan
private function applyUserFilter($query)
{
    if ($this->shouldFilterByUser()) {
        $query->where('user_id', auth()->id());
    }
    return $query;
}
```

## Contoh Perhitungan

### Persentase Perubahan Transaksi

- **Hari ini**: 10 transaksi
- **Kemarin**: 8 transaksi
- **Persentase**: +25% (peningkatan)

- **Hari ini**: 5 transaksi
- **Kemarin**: 10 transaksi
- **Persentase**: -50% (penurunan)

- **Hari ini**: 10 transaksi
- **Kemarin**: 0 transaksi
- **Persentase**: +100% (peningkatan dari 0)

### Rata-rata Transaksi

**Contoh untuk User dengan Role Admin/Kasir:**
- **Total Penjualan Hari Ini** (user tersebut): Rp 5.000.000
- **Jumlah Transaksi Hari Ini** (user tersebut): 10 transaksi
- **Rata-rata Transaksi**: Rp 5.000.000 / 10 = **Rp 500.000**

**Contoh untuk User dengan Role Lain (Super Admin):**
- **Total Penjualan Hari Ini** (semua user): Rp 20.000.000
- **Jumlah Transaksi Hari Ini** (semua user): 50 transaksi
- **Rata-rata Transaksi**: Rp 20.000.000 / 50 = **Rp 400.000**

## Testing

Untuk menguji fitur ini:

1. Login sebagai user dengan role `admin` atau `kasir`
2. Buka dashboard
3. Verifikasi bahwa hanya transaksi user tersebut yang ditampilkan
4. Cek persentase perubahan dibandingkan hari kemarin
5. Login sebagai user dengan role lain (misalnya `super_admin`)
6. Verifikasi bahwa semua transaksi ditampilkan
