# API Dokumentasi

Dokumentasi berikut merangkum endpoint yang tersedia untuk aplikasi kasir versi mobile. Seluruh endpoint berada di bawah prefix `https://<host>/api`.

## Autentikasi

Semua endpoint (kecuali `POST /api/login`) membutuhkan header `Authorization: Bearer {token}`. Token diperoleh lewat login dan dikelola oleh Laravel Sanctum.

### POST `/api/login`
Login dengan kredensial kasir dan mendapatkan token akses.

| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|------------|
| `username` | string | Ya | Username kasir |
| `password` | string | Ya | Password kasir |
| `device_name` | string | Tidak | Nama perangkat (default user-agent / `mobile`) |

**Response 200**
```json
{
  "token": "1|Q0k...ZpQ",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Super Admin",
    "username": "admin",
    "email": "admin@example.com",
    "roles": ["admin"]
  }
}
```

### POST `/api/logout`
Menghapus token aktif pengguna. Response:
```json
{ "message": "Berhasil keluar." }
```

### GET `/api/me`
Mendapatkan profil pengguna yang sedang login. Mengembalikan struktur `user` seperti pada login.

## Produk

### GET `/api/products`
Daftar produk aktif (paginate).

Query opsional: `q` (string, pencarian nama/sku/barcode), `per_page` (integer, default 20).

**Response 200**
```json
{
  "data": [
    {
      "id": 12,
      "name": "Air Mineral 1L",
      "sku": "AIRM1L",
      "barcode": "8991234567890",
      "unit": "botol",
      "price": 5000,
      "cost_price": 3000,
      "stock": 42,
      "stock_alert": 5,
      "is_active": true,
      "category": {
        "id": 2,
        "name": "Minuman",
        "slug": "minuman"
      },
      "created_at": "2025-01-07T09:12:01+07:00",
      "updated_at": "2025-01-07T09:12:01+07:00"
    }
  ],
  "links": { "...": "..." },
  "meta": { "...": "..." }
}
```

### GET `/api/products/{id}`
Detail produk aktif berdasarkan ID. Mengembalikan struktur seperti item pada daftar.

### GET `/api/products/lookup?barcode={value}`
Pencarian produk melalui barcode atau SKU.

- `barcode` (query, wajib): nilai barcode/sku.
- Respon 200 sama seperti detail produk.
- Respon 404 jika tidak ditemukan.
- Respon 422 jika parameter barcode kosong.

## Pelanggan

### GET `/api/customers`
Daftar pelanggan aktif (paginate).

Query opsional: `q` (mencari `name`, `phone`, `email`), `per_page` (default 20).

Response memuat data pelanggan dengan atribut `id`, `name`, `email`, `phone`, `address`, `city`, `state`, `postal_code`, `notes`, `is_active`, dan timestamp.

### GET `/api/customers/{id}`
Detail pelanggan aktif berdasarkan ID. Mengembalikan struktur sama dengan daftar.

## Transaksi

### GET `/api/transactions`
Daftar transaksi terbaru (paginate).

Query opsional:
- `start_date` (YYYY-MM-DD)
- `end_date` (YYYY-MM-DD)
- `q` (pencarian `invoice_number`)
- `per_page` (default 15)

Response memuat koleksi `TransactionResource` beserta relasi `customer` dan `user` bila tersedia.

### GET `/api/transactions/{id}`
Detail transaksi lengkap termasuk item dan produk.

**Response Contoh**
```json
{
  "data": {
    "id": 25,
    "invoice_number": "INV-20250107-0005",
    "customer_id": 4,
    "user_id": 1,
    "subtotal": 50000,
    "discount_amount": 5000,
    "discount_percent": 0,
    "total": 45000,
    "amount_paid": 50000,
    "change_due": 5000,
    "profit": 12000,
    "payment_method": "cash",
    "status": "completed",
    "notes": null,
    "created_at": "2025-01-07T10:15:44+07:00",
    "updated_at": "2025-01-07T10:15:44+07:00",
    "customer": { "...": "..." },
    "user": { "...": "..." },
    "items": [
      {
        "id": 40,
        "product_id": 12,
        "quantity": 2,
        "price": 20000,
        "cost_price": 15000,
        "total": 40000,
        "profit": 10000,
        "product": { "...": "..." }
      }
    ]
  }
}
```

### POST `/api/transactions`
Membuat transaksi baru. Gunakan content-type `application/json`.

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `customer_id` | integer | Tidak | ID pelanggan (null untuk pelanggan umum) |
| `discount_amount` | numeric | Tidak | Diskon nominal |
| `discount_percent` | numeric | Tidak | Diskon persen |
| `amount_paid` | numeric | Ya | Jumlah pembayaran |
| `payment_method` | string | Ya | Metode pembayaran, default `cash` |
| `notes` | string | Tidak | Catatan tambahan |
| `items` | array | Ya | Daftar item |
| `items[].product_id` | integer | Ya | ID produk |
| `items[].quantity` | integer | Ya | Kuantitas |
| `items[].price` | numeric | Ya | Harga jual per unit |
| `items[].cost_price` | numeric | Tidak | Harga modal (default data produk) |

**Response 201**
```json
{
  "message": "Transaksi berhasil dibuat.",
  "data": {
    "id": 26,
    "invoice_number": "INV-20250107-0006",
    "...": "..."
  }
}
```

**Validasi yang perlu diperhatikan**
- `items` minimal berisi satu produk aktif, stok akan dikurangi otomatis.
- `amount_paid` tidak boleh lebih kecil dari `total`.
- Jika stok produk kurang, API mengembalikan status 422 dengan pesan validasi.

## Status Kode Umum
- `200 OK` – permintaan berhasil.
- `201 Created` – transaksi berhasil dibuat.
- `401 Unauthorized` – token hilang/invalid.
- `404 Not Found` – resource tidak tersedia atau nonaktif.
- `422 Unprocessable Entity` – validasi gagal.

## Testing

Gunakan PHPUnit untuk menguji alur login hingga transaksi:
```bash
php artisan test --filter=TransactionApiTest
```

Pastikan `.env.testing` dikonfigurasi dengan database terpisah untuk pengujian.
