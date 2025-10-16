<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    /**
     * @var array<int, string>
     */
    protected array $errors = [];

    protected int $created = 0;

    protected int $updated = 0;

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // heading row occupies row 1

                if ($this->rowIsBlank($row)) {
                    continue;
                }

                $payload = $this->transformRow($row);

                $validator = Validator::make($payload, [
                    'name' => ['required', 'string', 'max:190'],
                    'sku' => ['required', 'string', 'max:50'],
                    'barcode' => ['nullable', 'string', 'max:50'],
                    'category_name' => ['nullable', 'string', 'max:190'],
                    'unit' => ['required', 'string', 'max:30'],
                    'price' => ['required', 'numeric', 'min:0'],
                    'cost_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
                    'stock' => ['nullable', 'integer', 'min:0'],
                    'stock_alert' => ['nullable', 'integer', 'min:0'],
                    'description' => ['nullable', 'string'],
                    'is_active' => ['boolean'],
                ], [], [
                    'name' => "baris {$rowNumber} kolom nama",
                    'sku' => "baris {$rowNumber} kolom sku",
                    'barcode' => "baris {$rowNumber} kolom barcode",
                    'category_name' => "baris {$rowNumber} kolom kategori",
                    'unit' => "baris {$rowNumber} kolom satuan",
                    'price' => "baris {$rowNumber} kolom harga_jual",
                    'cost_price' => "baris {$rowNumber} kolom harga_modal",
                    'stock' => "baris {$rowNumber} kolom stok",
                    'stock_alert' => "baris {$rowNumber} kolom stok_minimum",
                    'description' => "baris {$rowNumber} kolom deskripsi",
                    'is_active' => "baris {$rowNumber} kolom aktif",
                ]);

                if ($validator->fails()) {
                    $this->pushErrors($rowNumber, $validator->errors()->messages());
                    continue;
                }

                $data = $validator->validated();

                $categoryId = null;

                if (! empty($data['category_name'])) {
                    $category = Category::firstOrCreate(
                        ['name' => $data['category_name']],
                        ['description' => null, 'is_active' => true]
                    );

                    $categoryId = $category->id;
                }

                $product = Product::where('sku', $data['sku'])->first();

                if (! empty($data['barcode'])) {
                    $barcodeConflict = Product::where('barcode', $data['barcode'])
                        ->when($product, fn ($query) => $query->where('id', '!=', $product->id))
                        ->exists();

                    if ($barcodeConflict) {
                        $this->pushErrors($rowNumber, [["Barcode {$data['barcode']} sudah digunakan oleh produk lain."]]);
                        continue;
                    }
                }

                $productPayload = Arr::only($data, [
                    'name',
                    'barcode',
                    'unit',
                    'price',
                    'cost_price',
                    'stock',
                    'stock_alert',
                    'description',
                    'is_active',
                ]);

                $productPayload['category_id'] = $categoryId;
                $productPayload['cost_price'] = $productPayload['cost_price'] ?? $productPayload['price'];
                $productPayload['stock'] = $productPayload['stock'] ?? 0;
                $productPayload['stock_alert'] = $productPayload['stock_alert'] ?? 0;

                if ($product) {
                    $product->update($productPayload);
                    $this->updated++;
                } else {
                    $productPayload['sku'] = $data['sku'];
                    Product::create($productPayload);
                    $this->created++;
                }
            }

            if (! empty($this->errors)) {
                throw ValidationException::withMessages(['import_file' => $this->errors]);
            }
        });
    }

    /**
     * Retrieve import errors.
     *
     * @return array<int, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function summary(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
        ];
    }

    /**
     * Transform a row into a normalized payload.
     */
    protected function transformRow(Collection $row): array
    {
        $row = $row->mapWithKeys(function ($value, $key) {
            return [strtolower(trim((string) $key)) => $value];
        });

        $price = $this->toNumber($row->get('harga_jual'));
        $costPrice = $this->toNumber($row->get('harga_modal'));

        // cost price cannot exceed price and defaults to price when empty
        if ($costPrice !== null && $price !== null && $costPrice > $price) {
            $costPrice = $price;
        }

        return [
            'name' => $this->trimValue($row->get('nama')),
            'sku' => $this->trimValue($row->get('sku')),
            'barcode' => $this->trimValue($row->get('barcode')),
            'category_name' => $this->trimValue($row->get('kategori')),
            'unit' => $this->trimValue($row->get('satuan')),
            'price' => $price,
            'cost_price' => $costPrice,
            'stock' => $this->toInteger($row->get('stok')),
            'stock_alert' => $this->toInteger($row->get('stok_minimum')),
            'description' => $this->trimValue($row->get('deskripsi')),
            'is_active' => $this->toBoolean($row->get('aktif')),
        ];
    }

    protected function pushErrors(int $rowNumber, array $messages): void
    {
        foreach ($messages as $message) {
            if (is_array($message)) {
                foreach ($message as $text) {
                    $this->errors[] = "Baris {$rowNumber}: {$text}";
                }
                continue;
            }

            $this->errors[] = "Baris {$rowNumber}: {$message}";
        }
    }

    protected function trimValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function toNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $raw = trim((string) $value);
        $raw = str_replace("\u{00A0}", '', $raw); // strip non-breaking space
        $raw = preg_replace('/[^0-9,.\-]/', '', $raw);

        if ($raw === null || $raw === '') {
            return null;
        }

        if (str_contains($raw, ',') && str_contains($raw, '.')) {
            $normalized = str_replace('.', '', $raw);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($raw, ',')) {
            $normalized = str_replace(',', '.', $raw);
        } else {
            $normalized = $raw;
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    protected function toInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $normalized = preg_replace('/[^0-9\-]/', '', (string) $value);

        return $normalized === '' ? null : (int) $normalized;
    }

    protected function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $stringValue = strtolower(trim((string) $value));

        if ($stringValue === '') {
            return true;
        }

        return in_array($stringValue, ['1', 'true', 'ya', 'yes', 'aktif'], true);
    }

    protected function rowIsBlank(Collection $row): bool
    {
        return $row->filter(function ($value) {
            if ($value === null) {
                return false;
            }

            if (is_string($value)) {
                return trim($value) !== '';
            }

            return $value !== '';
        })->isEmpty();
    }
}
