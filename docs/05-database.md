# Database Design — MySQL

## 1. Prinsip

- Gunakan InnoDB.
- Primary key menggunakan BIGINT unsigned kecuali ada alasan lain.
- Gunakan foreign key untuk menjaga referential integrity.
- Gunakan decimal untuk nilai uang.
- Timestamp menggunakan kolom Laravel yang sesuai.
- Hindari hard delete untuk master yang memiliki histori transaksi.
- Nomor dokumen bisnis harus unik.
- Migrasi frontend (Vue.js + Inertia.js) tidak mengubah skema database maupun kontrak data; seluruh aturan di atas tetap berlaku.

## 2. Entity Overview

```text
users
  │
  ├──── sales
  │       └──── sale_items ──── products
  │
  ├──── purchases
  │       └──── purchase_items ─ products
  │
  └──── stock_adjustments

categories ──── products ──── units
                   │
                   └──── stock_movements

suppliers ──── purchases
```

## 3. users

Kolom minimal:
- id
- name
- email
- password
- role
- timestamps

Role minimal:
- admin
- cashier
- warehouse
- manager

Catatan: implementasi role dapat menggunakan enum/string atau mekanisme role package sesuai keputusan proyek.

## 4. categories

- id
- name
- description nullable
- is_active
- timestamps

Index/constraint:
- name dapat diberi unique jika business rule mengharuskannya.

## 5. units

- id
- name
- symbol
- is_active
- timestamps

Contoh:
- pcs
- box
- kg
- liter

## 6. suppliers

- id
- code nullable/unique
- name
- phone nullable
- email nullable
- address nullable
- is_active
- timestamps

## 7. products

- id
- category_id FK
- unit_id FK
- sku unique
- barcode nullable unique
- name
- purchase_price decimal(15,2)
- selling_price decimal(15,2)
- stock decimal(15,3) atau integer sesuai karakteristik produk
- minimum_stock decimal(15,3)
- image_path nullable — path relatif foto produk di disk penyimpanan `public` (`storage/app/public/products/…`, diakses via `storage:link` → `/storage/products/...`); tidak menyimpan binary di DB
- is_active
- timestamps

Catatan:
Gunakan integer jika seluruh produk selalu berupa satuan bulat. Gunakan decimal jika ada produk yang dapat dijual dalam pecahan.

## 8. purchases

- id
- supplier_id FK
- user_id FK
- purchase_number unique
- purchase_date
- status
- total_amount
- notes nullable
- timestamps

Status:
- draft
- completed
- cancelled

## 9. purchase_items

- id
- purchase_id FK
- product_id FK
- quantity
- unit_price
- subtotal
- timestamps

## 10. sales

- id
- user_id FK
- sale_number unique
- sale_date
- subtotal
- discount_amount
- tax_amount
- grand_total
- paid_amount
- change_amount
- payment_method
- status
- timestamps

Status:
- draft
- completed
- cancelled

Payment method contoh:
- cash
- transfer
- qris
- card

## 11. sale_items

- id
- sale_id FK
- product_id FK
- quantity
- unit_price
- discount_amount
- subtotal
- timestamps

## 12. stock_movements

- id
- product_id FK
- user_id FK nullable
- movement_type
- quantity
- stock_before
- stock_after
- reference_type nullable
- reference_id nullable
- notes nullable
- created_at

Movement type contoh:
- purchase_in
- sale_out
- adjustment
- return_in
- void_reversal

`reference_type` + `reference_id` dipakai untuk menelusuri sumber perubahan stok.

## 13. stock_adjustments

- id
- product_id FK
- user_id FK
- quantity_before
- quantity_after
- difference
- reason
- status
- timestamps

## 14. Relationships

### Product
- belongsTo Category
- belongsTo Unit
- hasMany SaleItem
- hasMany PurchaseItem
- hasMany StockMovement

### Sale
- belongsTo User
- hasMany SaleItem

### Purchase
- belongsTo Supplier
- belongsTo User
- hasMany PurchaseItem

### Supplier
- hasMany Purchase

### Category
- hasMany Product

### Unit
- hasMany Product

## 15. Important Constraints

### Product
SKU wajib unik.

### Sale
Sale number wajib unik.

### Purchase
Purchase number wajib unik.

### Stock
Stock tidak boleh menjadi negatif kecuali business rule secara eksplisit mengizinkannya.

### Transaction
Finalisasi penjualan dan penerimaan harus atomic.

## 16. Money

Gunakan:

```text
DECIMAL(15,2)
```

Jangan gunakan FLOAT untuk nilai uang.

## 17. Index Strategy

Index minimal yang dipertimbangkan:
- products.sku
- products.barcode
- products.category_id
- products.is_active
- sales.sale_number
- sales.sale_date
- sales.user_id
- purchases.purchase_number
- purchases.purchase_date
- stock_movements.product_id
- stock_movements.created_at

Index final ditentukan berdasarkan query dan kebutuhan nyata.

## 18. Migration Rules

- Semua schema change melalui migration.
- Jangan mengubah database production secara manual jika perubahan seharusnya dapat direproduksi melalui migration.
- Migration harus memiliki nama yang jelas.
- Foreign key harus dipertimbangkan terhadap lifecycle data.

## 19. Seed Data

Seeder development minimal menyediakan:
- role/user demo,
- kategori demo,
- satuan,
- supplier demo,
- beberapa produk.

Credential demo tidak boleh digunakan sebagai credential production.
