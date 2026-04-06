<?php

namespace App\Http\Requests;

use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResidentRequest extends FormRequest
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
            'unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(fn ($query) => $query->where('community_id', $community->id)),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'resident_type' => ['required', 'string', Rule::in(['owner', 'tenant'])],
            'pays_administration' => ['required', 'boolean'],
            'is_active' => [
                'required',
                'boolean',
                function ($attribute, $value, $fail) use ($community) {
                    if ($value && $this->input('resident_type') === 'tenant') {
                        $residentId = $this->route('resident')->id ?? null;

                        $exists = Resident::where('community_id', $community->id)
                            ->where('unit_id', $this->input('unit_id'))
                            ->where('resident_type', 'tenant')
                            ->where('is_active', true)
                            ->when($residentId, fn ($q) => $q->where('id', '!=', $residentId))
                            ->exists();

                        if ($exists) {
                            $fail('The unit already has an active tenant. You must deactivate them first.');
                        }
                    }
                },
            ],
        ];
    }
}
