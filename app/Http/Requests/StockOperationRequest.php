<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $routeName = $this->route()?->getName();

        $rules = [
            'item_id' => 'required|exists:items,id',
            'notes' => 'nullable|string|max:3000',
        ];

        if ($routeName === 'backoffice.stock.adjust') {
            return array_merge($rules, [
                'adjustment_scope' => ['required', Rule::in(['warehouse', 'technician'])],
                'technician_id' => 'nullable|exists:users,id',
                'quantity' => 'required|integer|not_in:0',
            ]);
        }

        return array_merge($rules, [
            'technician_id' => [
                Rule::requiredIf(in_array($routeName, [
                    'backoffice.stock.transfer',
                    'backoffice.stock.return',
                    'backoffice.stock.consume',
                ], true)),
                'nullable',
                'exists:users,id',
            ],
            'machine_id' => [
                // Obrigatório no consumo para alimentar o histórico da máquina.
                Rule::requiredIf($routeName === 'backoffice.stock.consume'),
                'nullable',
                'exists:machines,id',
            ],
            'quantity' => 'required|integer|min:1',
        ]);
    }

    public function messages(): array
    {
        return [
            'machine_id.required' => __('Selecione a máquina/nº de série a que se destina o consumo.'),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (
                $this->route()?->getName() === 'backoffice.stock.adjust' &&
                $this->input('adjustment_scope') === 'technician' &&
                !$this->filled('technician_id')
            ) {
                $validator->errors()->add('technician_id', __('Selecione o técnico para ajustar o stock da carrinha.'));
            }
        });
    }
}
