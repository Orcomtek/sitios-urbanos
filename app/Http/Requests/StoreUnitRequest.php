<?php

namespace App\Http\Requests;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(TenantContext $tenantContext): array
    {
        $community = $tenantContext->require();

        return [

            'identifier' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units')->where(fn ($query) => $query->where('community_id', $community->id)),
            ],
            'property_type' => ['required', 'string', Rule::in(['apartment', 'house', 'commercial', 'office', 'warehouse'])],
            'status' => ['required', 'string', Rule::in(['occupied', 'available', 'maintenance'])],
            'has_parking' => ['required', 'boolean'],
            'parking_count' => ['nullable', 'integer', 'min:1'],
            'parking_identifiers' => ['nullable', 'array'],
            'parking_identifiers.*' => ['string'],
            'has_storage' => ['required', 'boolean'],
            'storage_count' => ['nullable', 'integer', 'min:1'],
            'storage_identifiers' => ['nullable', 'array'],
            'storage_identifiers.*' => ['string'],
        ];
    }
}
