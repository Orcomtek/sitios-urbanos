<?php

namespace App\Http\Requests;

use App\Enums\PqrsStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePqrsStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(PqrsStatus::class)],
            'admin_response' => ['nullable', 'string'],
        ];
    }
}
