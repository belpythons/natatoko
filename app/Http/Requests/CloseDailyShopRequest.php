<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseDailyShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'actual_cash' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:daily_consignments,id',
            'items.*.remaining_stock' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'actual_cash.required' => 'Actual cash is required.',
            'actual_cash.numeric' => 'Actual cash must be a number.',
            'actual_cash.min' => 'Actual cash cannot be negative.',
            'items.required' => 'Items data is required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.id.required' => 'Item ID is required.',
            'items.*.id.integer' => 'Item ID must be an integer.',
            'items.*.id.exists' => 'Item does not exist.',
            'items.*.remaining_stock.required' => 'Remaining stock is required for each item.',
            'items.*.remaining_stock.numeric' => 'Remaining stock must be a number.',
            'items.*.remaining_stock.min' => 'Remaining stock cannot be negative.',
        ];
    }
}
