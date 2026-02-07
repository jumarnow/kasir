<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        $productId = $this->route('product');

        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:190'],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($productId)],
            'unit' => ['required', 'string', 'max:30'],
            'pricing_type' => ['required', 'in:per_unit,per_dimension'],
            'price' => ['nullable', 'numeric', 'min:0', 'required_if:pricing_type,per_unit'],
            'price_2' => ['nullable', 'numeric', 'min:0'],
            'price_3' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'price_per_meter' => ['nullable', 'numeric', 'min:0', 'required_if:pricing_type,per_dimension'],
            'price_unit' => ['nullable', 'in:per_m2,per_cm2'],
            'min_width' => ['nullable', 'numeric', 'min:0'],
            'min_length' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_alert' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
