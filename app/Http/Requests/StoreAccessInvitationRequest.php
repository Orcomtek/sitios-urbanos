<?php

namespace App\Http\Requests;

use App\Models\Resident;
use App\Models\Unit;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccessInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenantContext = app(TenantContext::class)->get();
        $user = $this->user();
        $unitId = $this->input('unit_id');

        if (! $unitId) {
            return false;
        }

        $unit = Unit::where('community_id', $tenantContext->id)->find($unitId);

        if (! $unit) {
            return false;
        }

        // Admins can create for any unit in the community.
        if ($user->hasRoleInCommunity($tenantContext, 'admin')) {
            return true;
        }

        // Residents can only create for their own active units.
        if ($user->hasRoleInCommunity($tenantContext, 'resident')) {
            return Resident::where('community_id', $tenantContext->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where('unit_id', $unitId)
                ->exists();
        }

        return false;
    }

    public function rules(): array
    {
        $tenantContext = app(TenantContext::class)->get();

        return [
            'unit_id' => [
                'required',
                'integer',
                // Using rule array format or manually checked in authorize above.
                // We'll trust authorize for deep logic, just check exists here.
                "exists:units,id,community_id,{$tenantContext->id}",
            ],
            'visitor_id' => [
                'nullable',
                'integer',
                "exists:visitors,id,community_id,{$tenantContext->id}",
            ],
            'type' => ['nullable', 'string', 'in:qr,manual_code'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
