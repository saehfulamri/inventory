<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'new_stock' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'new_stock.required' => 'Stok baru wajib diisi.',
            'new_stock.numeric' => 'Stok baru harus berupa angka.',
            'new_stock.min' => 'Stok baru tidak boleh negatif.',
            'reason.required' => 'Alasan penyesuaian wajib diisi.',
            'reason.min' => 'Alasan penyesuaian minimal 3 karakter.',
        ];
    }
}
