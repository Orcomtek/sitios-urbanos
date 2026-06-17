<?php

namespace App\Http\Requests\Financial;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference_number' => ['nullable', 'string'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
