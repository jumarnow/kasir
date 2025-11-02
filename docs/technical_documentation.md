# Dokumentasi Teknis Aplikasi Kasir

## Daftar Isi
1. [Pendahuluan](#pendahuluan)
2. [Arsitektur Sistem](#arsitektur-sistem)
3. [Entity Relationship Diagram](#entity-relationship-diagram)
4. [Flowchart Proses Bisnis](#flowchart-proses-bisnis)
5. [Struktur Database](#struktur-database)
6. [API Documentation](#api-documentation)
7. [Panduan Deployment](#panduan-deployment)

## Pendahuluan

Aplikasi Kasir adalah sistem point of sale (POS) berbasis web yang dibangun menggunakan framework Laravel. Aplikasi ini dirancang untuk membantu pemilik usaha kecil dan menengah dalam mengelola transaksi penjualan, inventaris produk, dan pelanggan.

### Fitur Utama
- Manajemen produk dan kategori
- Manajemen pelanggan
- Proses transaksi penjualan
- Laporan penjualan dan inventaris
- Manajemen pengguna dan hak akses
- Barcode scanning dan printing

### Teknologi yang Digunakan
- **Backend**: Laravel 10.x
- **Database**: MySQL
- **Frontend**: Blade Template, Bootstrap, jQuery
- **Autentikasi**: Laravel Fortify
- **Otorisasi**: Role-based Access Control

## Arsitektur Sistem

Aplikasi Kasir menggunakan arsitektur MVC (Model-View-Controller) dengan tambahan Service Layer untuk menangani logika bisnis yang kompleks.

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    Client   │────▶│  Controller │────▶│   Service   │────▶│    Model    │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
       │                   │                   │                   │
       │                   │                   │                   │
       ▼                   ▼                   ▼                   ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                               Database                                  │
└─────────────────────────────────────────────────────────────────────────┘
```

### Komponen Utama
1. **Models**: Representasi data dan logika bisnis dasar
2. **Controllers**: Menangani request HTTP dan response
3. **Services**: Menangani logika bisnis kompleks
4. **Views**: Tampilan untuk pengguna (Blade templates)

## Entity Relationship Diagram

```
┌───────────────┐       ┌───────────────┐       ┌───────────────┐
│    Category   │       │    Product    │       │  Transaction  │
├───────────────┤       ├───────────────┤       ├───────────────┤
│ id            │       │ id            │       │ id            │
│ name          │       │ category_id   │◄──────┤ invoice_number│
│ slug          │◄──────┤ name          │       │ user_id       │
│ description   │       │ slug          │       │ customer_id   │
│ created_at    │       │ sku           │       │ subtotal      │
│ updated_at    │       │ barcode       │       │ discount_amount│
└───────────────┘       │ unit          │       │ discount_percent│
                        │ price         │       │ total         │
                        │ cost_price    │       │ amount_paid   │
                        │ stock         │       │ change_due    │
                        │ stock_alert   │       │ profit        │
                        │ description   │       │ payment_method│
                        │ is_active     │       │ status        │
                        │ created_at    │       │ notes         │
                        │ updated_at    │       │ created_at    │
                        └───────────────┘       │ updated_at    │
                                │               └───────────────┘
                                │                      │
                                ▼                      │
                        ┌───────────────┐              │
                        │TransactionItem│              │
                        ├───────────────┤              │
                        │ id            │              │
                        │ transaction_id│◄─────────────┘
                        │ product_id    │◄─────────────┐
                        │ quantity      │              │
                        │ price         │              │
                        │ cost_price    │              │
                        │ created_at    │              │
                        │ updated_at    │              │
                        └───────────────┘              │
                                                       │
┌───────────────┐       ┌───────────────┐             │
│     User      │       │   Customer    │             │
├───────────────┤       ├───────────────┤             │
│ id            │       │ id            │             │
│ name          │       │ name          │             │
│ email         │       │ email         │             │
│ username      │       │ phone         │             │
│ password      │       │ address       │             │
│ created_at    │       │ created_at    │             │
│ updated_at    │       │ updated_at    │             │
└───────────────┘       └───────────────┘             │
       │                                              │
       │                                              │
       ▼                                              │
┌───────────────┐                                     │
│     Role      │                                     │
├───────────────┤                                     │
│ id            │                                     │
│ name          │                                     │
│ created_at    │                                     │
│ updated_at    │                                     │
└───────────────┘                                     │
       │                                              │
       │                                              │
       ▼                                              │
┌───────────────┐                                     │
│  Permission   │                                     │
├───────────────┤                                     │
│ id            │                                     │
│ name          │                                     │
│ created_at    │                                     │
│ updated_at    │                                     │
└───────────────┘                                     │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
                                                      │
```

## Flowchart Proses Bisnis

### Proses Transaksi Penjualan

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    Start    │────▶│  Scan/Input │────▶│   Add to    │────▶│  Calculate  │
│             │     │   Product   │     │   Cart      │     │   Total     │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                                                                   │
                                                                   │
                                                                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    Print    │◀────│   Save to   │◀────│   Process   │◀────│   Input     │
│   Receipt   │     │  Database   │     │   Payment   │     │  Payment    │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
       │
       │
       ▼
┌─────────────┐
│     End     │
│             │
└─────────────┘
```

### Proses Manajemen Stok

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    Start    │────▶│  Check Stock│────▶│ Stock Below │──Yes──┐
│             │     │   Levels    │     │  Threshold? │       │
└─────────────┘     └─────────────┘     └─────────────┘       │
                                               │              │
                                               No             │
                                               │              │
                                               ▼              ▼
                                        ┌─────────────┐ ┌─────────────┐
                                        │     End     │ │  Generate   │
                                        │             │ │   Alert     │
                                        └─────────────┘ └─────────────┘
                                                               │
                                                               │
                                                               ▼
                                                        ┌─────────────┐
                                                        │  Restock    │
                                                        │  Products   │
                                                        └─────────────┘
                                                               │
                                                               │
                                                               ▼
                                                        ┌─────────────┐
                                                        │  Update     │
                                                        │  Inventory  │
                                                        └─────────────┘
                                                               │
                                                               │
                                                               ▼
                                                        ┌─────────────┐
                                                        │     End     │
                                                        │             │
                                                        └─────────────┘
```

## Struktur Database

### Tabel: users
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| name | varchar(255) | Nama pengguna |
| email | varchar(255) | Email pengguna (unique) |
| username | varchar(255) | Username untuk login (unique) |
| password | varchar(255) | Password terenkripsi |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update terakhir |

### Tabel: categories
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| name | varchar(255) | Nama kategori |
| slug | varchar(255) | Slug untuk URL (unique) |
| description | text | Deskripsi kategori |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update terakhir |

### Tabel: products
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| category_id | bigint | Foreign key ke categories |
| name | varchar(255) | Nama produk |
| slug | varchar(255) | Slug untuk URL (unique) |
| sku | varchar(255) | Stock Keeping Unit (unique) |
| barcode | varchar(255) | Barcode produk |
| unit | varchar(255) | Satuan produk (pcs, kg, dll) |
| price | decimal(10,2) | Harga jual |
| cost_price | decimal(10,2) | Harga beli/modal |
| stock | int | Jumlah stok |
| stock_alert | int | Batas minimum stok |
| description | text | Deskripsi produk |
| is_active | boolean | Status aktif produk |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update terakhir |

### Tabel: customers
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| name | varchar(255) | Nama pelanggan |
| email | varchar(255) | Email pelanggan |
| phone | varchar(255) | Nomor telepon |
| address | text | Alamat pelanggan |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update terakhir |

### Tabel: transactions
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| invoice_number | varchar(255) | Nomor invoice (unique) |
| user_id | bigint | Foreign key ke users |
| customer_id | bigint | Foreign key ke customers |
| subtotal | decimal(10,2) | Subtotal transaksi |
| discount_amount | decimal(10,2) | Jumlah diskon |
| discount_percent | decimal(5,2) | Persentase diskon |
| total | decimal(10,2) | Total transaksi |
| amount_paid | decimal(10,2) | Jumlah yang dibayarkan |
| change_due | decimal(10,2) | Kembalian |
| profit | decimal(10,2) | Keuntungan |
| payment_method | varchar(255) | Metode pembayaran |
| status | varchar(255) | Status transaksi |
| notes | text | Catatan transaksi |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update terakhir |

### Tabel: transaction_items
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| transaction_id | bigint | Foreign key ke transactions |
| product_id | bigint | Foreign key ke products |
| quantity | int | Jumlah item |
| price | decimal(10,2) | Harga jual saat transaksi |
| cost_price | decimal(10,2) | Harga modal saat transaksi |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update terakhir |

### Tabel: roles
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| name | varchar(255) | Nama role |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update terakhir |

### Tabel: permissions
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| name | varchar(255) | Nama permission |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update terakhir |

### Tabel: permission_role
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| permission_id | bigint | Foreign key ke permissions |
| role_id | bigint | Foreign key ke roles |

### Tabel: role_user
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| role_id | bigint | Foreign key ke roles |
| user_id | bigint | Foreign key ke users |

## API Documentation

### Autentikasi

#### Login
- **URL**: `/api/login`
- **Method**: `POST`
- **Request Body**:
  ```json
  {
    "username": "admin",
    "password": "password"
  }
  ```
- **Response**:
  ```json
  {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "username": "admin"
    }
  }
  ```

### Produk

#### Daftar Produk
- **URL**: `/api/products`
- **Method**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**:
  - `page`: Nomor halaman (default: 1)
  - `per_page`: Jumlah item per halaman (default: 15)
  - `search`: Kata kunci pencarian
  - `category_id`: Filter berdasarkan kategori
  - `is_active`: Filter berdasarkan status aktif
- **Response**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "name": "Produk A",
        "category": {
          "id": 1,
          "name": "Kategori A"
        },
        "sku": "SKU12345",
        "barcode": "123456789",
        "price": 10000,
        "stock": 100,
        "is_active": true
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 10,
      "per_page": 15,
      "total": 150
    }
  }
  ```

#### Detail Produk
- **URL**: `/api/products/{id}`
- **Method**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "data": {
      "id": 1,
      "name": "Produk A",
      "category": {
        "id": 1,
        "name": "Kategori A"
      },
      "sku": "SKU12345",
      "barcode": "123456789",
      "unit": "pcs",
      "price": 10000,
      "cost_price": 8000,
      "stock": 100,
      "stock_alert": 10,
      "description": "Deskripsi produk",
      "is_active": true,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  }
  ```

### Transaksi

#### Buat Transaksi
- **URL**: `/api/transactions`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **Request Body**:
  ```json
  {
    "customer_id": 1,
    "items": [
      {
        "product_id": 1,
        "quantity": 2,
        "price": 10000
      },
      {
        "product_id": 2,
        "quantity": 1,
        "price": 15000
      }
    ],
    "discount_percent": 5,
    "discount_amount": 0,
    "payment_method": "cash",
    "amount_paid": 50000,
    "notes": "Catatan transaksi"
  }
  ```
- **Response**:
  ```json
  {
    "data": {
      "id": 1,
      "invoice_number": "INV-20230101-0001",
      "customer": {
        "id": 1,
        "name": "Pelanggan A"
      },
      "subtotal": 35000,
      "discount_amount": 1750,
      "discount_percent": 5,
      "total": 33250,
      "amount_paid": 50000,
      "change_due": 16750,
      "profit": 9000,
      "payment_method": "cash",
      "status": "completed",
      "notes": "Catatan transaksi",
      "items": [
        {
          "product_id": 1,
          "name": "Produk A",
          "quantity": 2,
          "price": 10000,
          "subtotal": 20000
        },
        {
          "product_id": 2,
          "name": "Produk B",
          "quantity": 1,
          "price": 15000,
          "subtotal": 15000
        }
      ],
      "created_at": "2023-01-01T00:00:00.000000Z"
    }
  }
  ```

## Panduan Deployment

### Persyaratan Sistem
- PHP 8.1 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Composer
- Node.js dan NPM
- Web server (Apache/Nginx)

### Langkah-langkah Deployment

#### 1. Clone Repository
```bash
git clone https://github.com/username/kasir.git
cd kasir
```

#### 2. Install Dependencies
```bash
composer install
npm install
npm run build
```

#### 3. Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi database:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasir
DB_USERNAME=root
DB_PASSWORD=
```

#### 4. Migrasi dan Seeding Database
```bash
php artisan migrate
php artisan db:seed
```

#### 5. Konfigurasi Web Server

**Apache (.htaccess sudah disediakan di folder public)**

**Nginx**
```
server {
    listen 80;
    server_name example.com;
    root /path/to/kasir/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 6. Pengaturan Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 7. Deployment dengan Docker (Opsional)
Aplikasi ini sudah dilengkapi dengan Dockerfile dan .dockerignore untuk deployment menggunakan Docker.

```bash
docker build -t kasir-app .
docker run -p 8000:80 kasir-app
```

### Akses Aplikasi
Setelah deployment berhasil, aplikasi dapat diakses melalui browser dengan URL yang telah dikonfigurasi. Gunakan kredensial default untuk login:

- Username: admin
- Password: password

Segera ubah password default setelah login pertama kali.