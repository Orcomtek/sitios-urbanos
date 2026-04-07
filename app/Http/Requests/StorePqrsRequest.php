<?php

namespace App\Http\Requests;

use App\Enums\PqrsType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePqrsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PqrsType::class)],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'is_anonymous' => ['required', 'boolean'],
        ];
    }
}
