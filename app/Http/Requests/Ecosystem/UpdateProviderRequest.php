<?php

namespace App\Http\Requests\Ecosystem;

use App\Enums\ProviderCategory;
use App\Enums\ProviderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Handled by controller using Policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['sometimes', 'required', 'string', Rule::enum(ProviderCategory::class)],
            'status' => ['sometimes', 'required', 'string', Rule::enum(ProviderStatus::class)],
            'is_recommended' => ['boolean'],
            'contact_details' => ['sometimes', 'required', 'array'],
            'contact_details.*.type' => ['required_with:contact_details', 'string', 'max:50'],
            'contact_details.*.value' => ['required_with:contact_details', 'string', 'max:255'],
        ];
    }
}
