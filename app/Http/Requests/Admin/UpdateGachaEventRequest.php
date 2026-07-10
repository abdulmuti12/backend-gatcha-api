<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGachaEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'cost_per_pull' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after:starts_at'],

            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.rarity' => ['required_with:items', 'in:common,rare,legendary'],
            'items.*.drop_rate' => ['required_with:items', 'numeric', 'min:0.01', 'max:100'],
            'items.*.image_url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        if (is_array($items)) {
            foreach ($items as $i => $item) {
                if (is_array($item) && ! isset($item['drop_rate']) && isset($item['drop_rate_percent'])) {
                    $items[$i]['drop_rate'] = $item['drop_rate_percent'];
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    public function messages(): array
    {
        return [
            'items.*.drop_rate.required_with' => 'Drop rate tiap item wajib diisi.',
            'ends_at.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
        ];
    }
}
