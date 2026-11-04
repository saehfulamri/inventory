<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:units,name'],
            'symbol' => ['required', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.unique' => 'Nama satuan sudah digunakan.',
            'symbol.required' => 'Simbol satuan wajib diisi.',
        ];
    }
}
