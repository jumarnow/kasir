<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'customer_id' => $this->customer_id,
            'user_id' => $this->user_id,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'discount_percent' => (float) $this->discount_percent,
            'total' => (float) $this->total,
            'amount_paid' => (float) $this->amount_paid,
            'change_due' => (float) $this->change_due,
            'profit' => (float) $this->profit,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'items' => TransactionItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
