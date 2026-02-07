# Usulan Sistem Pencatatan Pengeluaran & Laporan Profit

## Analisis Kebutuhan

Berdasarkan workflow Arjuna Percetakan:

| Kategori | Contoh | Frekuensi |
|----------|--------|-----------|
| **Pemasukan** | Penjualan produk | Harian |
| **Pengeluaran Operasional** | Kurir, pensil, bensin | Harian |
| **Pengeluaran Bahan Baku** | Tinta, spare part, material | Periodik |
| **Pengeluaran Bulanan** | Gaji, listrik, air, keamanan, sampah | Bulanan |

**Formula Profit:**
```
Profit = Penjualan Kotor - Modal Usaha (Semua Pengeluaran)
```

---

## Usulan Fitur

### 1. Modul Pengeluaran (Expenses)

**Tabel `expenses`:**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| category_id | bigint | FK ke expense_categories |
| amount | decimal | Jumlah pengeluaran |
| description | string | Keterangan |
| expense_date | date | Tanggal pengeluaran |
| receipt_image | string | Foto bukti (opsional) |

**Tabel `expense_categories`:**
| Kolom | Contoh Data |
|-------|-------------|
| Operasional Harian | Kurir, Bensin, ATK |
| Bahan Baku | Tinta, Kertas, Spare Part |
| Gaji Karyawan | Gaji bulanan |
| Utilitas | Listrik, Air, Internet |
| Lainnya | Keamanan, Sampah |

### 2. Dashboard Ringkasan Bulanan

```
┌─────────────────────────────────────────────┐
│           LAPORAN PROFIT FEBRUARI 2026      │
├─────────────────────────────────────────────┤
│ Penjualan Kotor          Rp  45.000.000     │
├─────────────────────────────────────────────┤
│ Modal Usaha:                                │
│   - Operasional Harian   Rp   2.500.000     │
│   - Bahan Baku           Rp  15.000.000     │
│   - Gaji Karyawan        Rp  10.000.000     │
│   - Utilitas             Rp   2.000.000     │
│   - Lainnya              Rp     500.000     │
│   ─────────────────────────────────────     │
│   Total Modal            Rp  30.000.000     │
├─────────────────────────────────────────────┤
│ PROFIT BERSIH            Rp  15.000.000     │
└─────────────────────────────────────────────┘
```

### 3. Export Excel

File Excel dengan 3 sheet:

**Sheet 1: Ringkasan**
- Total Penjualan Kotor
- Total Modal per Kategori
- Profit Bersih

**Sheet 2: Detail Penjualan**
- Daftar transaksi harian
- Subtotal per hari

**Sheet 3: Detail Pengeluaran**
- Daftar pengeluaran per kategori
- Subtotal per kategori

---

## Implementasi Teknis

### Database Migration
```php
// expenses table
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('expense_categories');
    $table->foreignId('user_id')->constrained();
    $table->decimal('amount', 15, 2);
    $table->string('description');
    $table->date('expense_date');
    $table->string('receipt_image')->nullable();
    $table->timestamps();
});

// expense_categories table
Schema::create('expense_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('type'); // daily, monthly, material
    $table->timestamps();
});
```

### Routes
```php
Route::resource('expenses', ExpenseController::class);
Route::resource('expense-categories', ExpenseCategoryController::class);
Route::get('reports/profit', [ReportController::class, 'profit']);
Route::get('reports/profit/export', [ReportController::class, 'exportProfit']);
```

---

## Prioritas Pengembangan

1. **Fase 1**: Kategori pengeluaran + CRUD pengeluaran
2. **Fase 2**: Laporan profit bulanan di dashboard
3. **Fase 3**: Export Excel

---

## Pertanyaan untuk Konfirmasi

1. Apakah perlu fitur upload foto bukti/struk?
2. Apakah perlu approval untuk pengeluaran besar?
3. Siapa saja yang boleh input pengeluaran (admin/kasir/semua)?
4. Apakah perlu notifikasi jika pengeluaran melebihi budget?
