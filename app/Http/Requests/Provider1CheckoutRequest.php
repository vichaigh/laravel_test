<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Provider1CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|gt:0',
            'currency' => 'required|string|size:3',
            'reference_id' => 'required|string|max:100',
            'return_url' => 'required|url',
            'cancel_url' => 'required|url',
            'customer_email' => 'nullable|email',
            'customer_name' => 'nullable|string|max:255',
        ];
    }
}
