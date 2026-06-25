<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'eksekutor_id' => ['nullable', 'exists:employees,id'],
            'eksekutor_2_id' => ['nullable', 'exists:employees,id'],
            'desainer_id' => ['nullable', 'exists:employees,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', 'in:tunai,qris,transfer'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.custom_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.finishing_id' => ['nullable', 'exists:finishings,id'],
            'items.*.display_id' => ['nullable', 'exists:displays,id'],
            'items.*.material_id' => ['nullable', 'exists:materials,id'],
            'items.*.width' => ['nullable', 'numeric', 'min:0'],
            'items.*.length' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    $paymentMethod = $this->input('payment_method');
                    if (in_array($paymentMethod, ['dp', 'pending', 'cod_kurir']) && empty($value)) {
                        $fail('Tanggal jatuh tempo wajib diisi untuk pembayaran DP, Pending, atau COD Kurir.');
                    }
                }
            ],
            'created_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'discount_amount' => toNumeric($this->input('discount_amount', 0)),
            'discount_percent' => toNumeric($this->input('discount_percent', 0)),
            'shipping_cost' => toNumeric($this->input('shipping_cost', 0)),
            'amount_paid' => toNumeric($this->input('amount_paid', 0)),
        ]);
    }
}
