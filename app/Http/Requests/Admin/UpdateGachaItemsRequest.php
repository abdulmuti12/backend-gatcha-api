<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGachaItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.rarity' => ['required', 'in:common,rare,legendary'],
            'items.*.drop_rate' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'items.*.image_url' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
