<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,transfer,qris,card'],
            'paid_amount' => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_date.required' => 'Tanggal wajib diisi.',
            'sale_date.date' => 'Tanggal tidak valid.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'paid_amount.required_if' => 'Jumlah pembayaran wajib diisi untuk pembayaran tunai.',
            'paid_amount.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'paid_amount.min' => 'Jumlah pembayaran tidak boleh negatif.',
            'items.required' => 'Keranjang belanja kosong.',
            'items.min' => 'Keranjang belanja kosong.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan.',
            'items.*.quantity.required' => 'Jumlah wajib diisi.',
            'items.*.quantity.min' => 'Jumlah harus lebih dari nol.',
        ];
    }
}
