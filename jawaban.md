# Analisis: Edit & Delete Transaksi

## 🤔 Pertanyaan
Apakah edit transaksi sebaiknya menggunakan file yang sama dengan create (`create.blade.php`)?

---

## 📋 Rekomendasi: **TIDAK** - Buat File Terpisah

### Alasan:

| Aspek | Create | Edit |
|-------|--------|------|
| **Data Flow** | Mulai dari kosong | Load dari database |
| **Cart State** | Empty array | Pre-filled dari `transaction.items` |
| **Invoice Number** | Generate baru | Sudah ada (readonly) |
| **Stok Logic** | Decrement | Restore + Recalculate |
| **Print Flow** | Setelah submit | Bisa langsung |
| **Form Action** | `store` | `update` |

### Kompleksitas Tinggi:
1. **JavaScript cart** harus di-populate dari existing items
2. **Stok produk** harus di-restore sebelum dihitung ulang
3. **Payment status** tidak boleh sembarangan diubah (already paid)
4. **Invoice number** harus readonly

---

## ✅ Solusi yang Direkomendasikan

### Opsi 1: Edit Terbatas (Simple) ⭐ RECOMMENDED
Buat halaman edit sederhana untuk mengubah:
- Status pesanan (`order_status`)
- Status pembayaran + tambah bayar
- Catatan (`notes`)
- Due date

**Tidak mengizinkan** edit items/qty/harga setelah transaksi dibuat.

**Pro:** Aman, cepat implementasi, mencegah manipulasi data.

### Opsi 2: Edit Penuh (Complex)
Buat `edit.blade.php` terpisah dengan logic:
1. Load existing transaction + items ke JavaScript
2. Restore stok produk yang sudah dikurangi
3. Allow edit items, qty, price
4. Recalculate stok saat submit

**Pro:** Fleksibel.
**Con:** Kompleks, rawan error stok, butuh audit trail.

---

## � Kasus: Kasir Salah Input

### Skenario Umum:
- Salah pilih produk
- Salah qty
- Salah harga
- Salah pelanggan

### Solusi: **Edit Window + Approval**

| Kondisi | Yang Boleh Edit | Approval |
|---------|-----------------|----------|
| < 30 menit setelah input | Kasir sendiri | Tidak perlu |
| > 30 menit | Admin/Kepala Toko | Perlu |
| Sudah lunas | Tidak boleh edit | Harus void + buat ulang |
| Sudah DP | Admin only | Perlu |

### Implementasi Hybrid (Recommended):

```php
public function canEdit(Transaction $transaction): bool
{
    // 1. Transaksi lunas tidak boleh edit
    if ($transaction->payment_status === 'paid') {
        return false;
    }
    
    // 2. Dalam 30 menit, kasir bisa edit sendiri
    $editWindow = $transaction->created_at->addMinutes(30);
    if (now()->lessThan($editWindow) && auth()->id() === $transaction->user_id) {
        return true;
    }
    
    // 3. Setelah 30 menit, hanya admin/kepala_toko
    return auth()->user()->hasAnyRole(['admin', 'kepala_toko', 'finance', 'manager']);
}
```

### Flow Koreksi Kesalahan:

```
┌─────────────────────────────────────────────────────────┐
│ Kasir salah input                                       │
├─────────────────────────────────────────────────────────┤
│ < 30 menit? ──Yes──> Edit langsung (restore stok dulu) │
│      │                                                  │
│      No                                                 │
│      ▼                                                  │
│ Status pending? ──Yes──> Minta approval Admin          │
│      │                                                  │
│      No (DP/Lunas)                                      │
│      ▼                                                  │
│ VOID transaksi + Buat ulang yang benar                 │
└─────────────────────────────────────────────────────────┘
```

### Audit Trail (Penting!):
```php
// Log setiap perubahan
TransactionLog::create([
    'transaction_id' => $transaction->id,
    'action' => 'edited',
    'old_data' => json_encode($oldData),
    'new_data' => json_encode($newData),
    'user_id' => auth()->id(),
    'reason' => $request->edit_reason, // Wajib isi alasan
]);
```

---

## �🗑️ Delete / Void Transaksi

### Rekomendasi: **Soft Delete + Void**

```php
// TransactionController
public function destroy(Transaction $transaction)
{
    // 1. Restore stok produk
    foreach ($transaction->items as $item) {
        $item->product->incrementStock($item->quantity);
    }
    
    // 2. Void (bukan delete)
    $transaction->update([
        'status' => 'voided',
        'voided_at' => now(),
        'voided_by' => auth()->id(),
    ]);
    
    // Atau soft delete
    $transaction->delete();
}
```

### Syarat Delete:
- Hanya transaksi dengan `payment_status = pending` yang boleh di-void
- Transaksi lunas/DP tidak boleh di-void (harus buat retur)

---

## 📁 Struktur File Baru (Jika Opsi 1)

```
resources/views/transactions/
├── index.blade.php      (existing)
├── create.blade.php     (existing)
├── show.blade.php       (existing - detail & print)
├── edit.blade.php       [NEW - edit status/notes only]
└── partials/
    └── ... (existing)
```

---

## ⚡ Quick Implementation (Opsi 1)

### 1. Tambah Route
```php
Route::get('transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
```

### 2. Controller Methods
```php
public function edit(Transaction $transaction)
{
    $transaction->load('items.product', 'customer');
    return view('transactions.edit', compact('transaction'));
}

public function update(Request $request, Transaction $transaction)
{
    $transaction->update($request->validated());
    return redirect()->route('transactions.show', $transaction)
        ->with('success', 'Transaksi berhasil diperbarui.');
}

public function destroy(Transaction $transaction)
{
    // Restore stok
    foreach ($transaction->items as $item) {
        $item->product->incrementStock($item->quantity);
    }
    
    $transaction->delete(); // soft delete
    
    return redirect()->route('transactions.index')
        ->with('success', 'Transaksi berhasil dibatalkan.');
}
```

### 3. Edit View (Simple)
Form sederhana untuk edit:
- Order Status dropdown
- Payment Status + Amount Paid
- Notes
- Due Date

---

## 🎯 Kesimpulan

| Pendekatan | Effort | Risk | Recommendation |
|------------|--------|------|----------------|
| Edit Status Only | Low | Low | ⭐ Start here |
| Edit Full Items | High | High | Phase 2 if needed |
| Soft Delete | Low | Low | ⭐ Implement |

**Mulai dengan Opsi 1** (edit terbatas), evaluasi apakah edit items benar-benar diperlukan. Kebanyakan bisnis retail/percetakan tidak mengizinkan edit transaksi yang sudah di-input untuk mencegah manipulasi.
