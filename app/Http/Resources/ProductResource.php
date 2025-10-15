<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'unit' => $this->unit,
            'price' => (float) $this->price,
            'cost_price' => (float) $this->cost_price,
            'stock' => (int) $this->stock,
            'stock_alert' => (int) $this->stock_alert,
            'is_active' => (bool) $this->is_active,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
