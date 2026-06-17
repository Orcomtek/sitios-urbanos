<?php

namespace App\Http\Requests\Financial;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountingNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'in:credit,debit'],
            'billing_concept_id' => ['required', 'exists:billing_concepts,id'],
            'description' => ['required', 'string', 'max:500'],
        ];
    }
}
