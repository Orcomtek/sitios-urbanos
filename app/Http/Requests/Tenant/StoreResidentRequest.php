<?php

namespace App\Http\Requests\Tenant;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled at the middleware/controller level,
        // but we ensure a TenantContext is present.
        return app(TenantContext::class)->get() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(TenantContext $context): array
    {
        return [
            'unit_id' => [
                'required',
                'integer',
                // Critical Tenant Isolation Validation Rule
                Rule::exists('units', 'id')->where('community_id', $context->require()->id),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'resident_type' => ['required', 'string', 'in:tenant,owner,co-resident'],
            'pays_administration' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
