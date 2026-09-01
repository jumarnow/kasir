<?php

namespace App\Imports;

use App\Models\RawMaterial;
use App\Models\RawMaterialMutation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class RawMaterialsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    protected array $errors = [];
    protected int $created = 0;
    protected int $updated = 0;

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                if ($this->rowIsBlank($row)) {
                    continue;
                }

                $payload = $this->transformRow($row);

                $validator = Validator::make($payload, [
                    'name' => ['required', 'string', 'max:190'],
                    'unit' => ['required', 'string', 'max:50'],
                    'barcode' => ['nullable', 'string', 'max:50'],
                    'sku' => ['nullable', 'string', 'max:50'],
                    'stock_awal' => ['nullable', 'integer', 'min:0'],
                    'batas_minimum' => ['nullable', 'integer', 'min:0'],
                    'deskripsi' => ['nullable', 'string'],
                ], [], [
                    'name' => "baris {$rowNumber} kolom nama",
                    'unit' => "baris {$rowNumber} kolom satuan",
                ]);

                if ($validator->fails()) {
                    $this->errors = array_merge($this->errors, $validator->errors()->all());
                    continue;
                }

                $data = $validator->validated();
                
                $sku = $data['sku'] ?? null;
                $rawMaterial = null;

                if ($sku) {
                    $rawMaterial = RawMaterial::where('sku', $sku)->first();
                }

                if ($rawMaterial) {
                    // Update
                    $rawMaterial->update([
                        'name' => $data['name'],
                        'unit' => $data['unit'],
                        'barcode' => $data['barcode'] ?? $rawMaterial->barcode,
                        'min_stock' => $data['batas_minimum'] ?? $rawMaterial->min_stock,
                        'description' => $data['deskripsi'] ?? $rawMaterial->description,
                    ]);
                    $this->updated++;
                } else {
                    // Create
                    $initialStock = $data['stock_awal'] ?? 0;
                    
                    $newMaterial = RawMaterial::create([
                        'name' => $data['name'],
                        'unit' => $data['unit'],
                        'barcode' => $data['barcode'],
                        'sku' => $sku ?? strtoupper(Str::random(8)),
                        'stock' => $initialStock,
                        'min_stock' => $data['batas_minimum'] ?? 0,
                        'description' => $data['deskripsi'],
                        'is_active' => true,
                    ]);
                    
                    // Create mutation if initial stock is > 0
                    if ($initialStock > 0) {
                        RawMaterialMutation::create([
                            'raw_material_id' => $newMaterial->id,
                            'type' => 'in',
                            'quantity' => $initialStock,
                            'previous_stock' => 0,
                            'current_stock' => $initialStock,
                            'description' => 'Stok awal dari import',
                        ]);
                    }
                    $this->created++;
                }
            }

            if (!empty($this->errors)) {
                throw ValidationException::withMessages($this->errors);
            }
        });
    }

    protected function rowIsBlank(Collection $row): bool
    {
        return $row->filter(function ($value) {
            return !is_null($value) && $value !== '';
        })->isEmpty();
    }

    protected function transformRow(Collection $row): array
    {
        return [
            'name' => $row['nama'] ?? null,
            'unit' => $row['satuan'] ?? null,
            'barcode' => $row['barcode'] ?? null,
            'sku' => $row['sku'] ?? null,
            'stock_awal' => $row['stok_awal'] ?? null,
            'batas_minimum' => $row['batas_minimum'] ?? null,
            'deskripsi' => $row['deskripsi'] ?? null,
        ];
    }

    public function summary(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
        ];
    }
}
