<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGachaEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cost_per_pull' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.rarity' => ['required', 'in:common,rare,legendary'],
            // drop_rate dikirim dalam persen (boleh desimal 2 digit), dikonversi ke basis point di controller
            'items.*.drop_rate' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'items.*.image_url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.drop_rate.required' => 'Drop rate tiap item wajib diisi.',
        ];
    }
}
