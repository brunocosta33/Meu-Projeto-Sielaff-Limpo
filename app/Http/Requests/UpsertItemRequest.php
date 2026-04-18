<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $itemId = $this->route('item')?->id;

        return [
            'reference' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'reference')->ignore($itemId),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'warehouse_stock' => 'nullable|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
