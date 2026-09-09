<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Provider2CheckoutRequest extends FormRequest
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
            'currency' => 'required|string|in:USD,EUR,GBP',
            'reference_id' => 'required|string|max:100',
            'return_url' => 'required|url',
            'cancel_url' => 'required|url',
            'customer_email' => 'required|email',
        ];
    }
}
