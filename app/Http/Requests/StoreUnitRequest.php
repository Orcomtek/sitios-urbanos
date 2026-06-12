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
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string'],
            'coefficient' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
