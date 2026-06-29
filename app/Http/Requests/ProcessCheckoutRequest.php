<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ProcessCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:watches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Keranjang belanja masih kosong.',
            'items.min' => 'Keranjang belanja masih kosong.',
            'items.*.product_id.exists' => 'Produk yang dipilih tidak ditemukan.',
            'items.*.quantity.min' => 'Jumlah produk minimal 1.',
        ];
    }
}
