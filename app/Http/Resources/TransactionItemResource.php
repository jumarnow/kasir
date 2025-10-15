<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'cost_price' => (float) $this->cost_price,
            'total' => (float) $this->total,
            'profit' => (float) $this->profit,
            'product' => ProductResource::make($this->whenLoaded('product')),
        ];
    }
}
