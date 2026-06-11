<?php

namespace App\Http\Controllers\Tenant\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\FinancialSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function edit(Request $request, string $community_slug)
    {
        $community = Community::where('slug', $community_slug)->firstOrFail();
        $settings = FinancialSetting::firstOrCreate(
            ['community_id' => $community->id],
            [
                'base_budget' => 0,
                'late_fee_interest_rate' => 0,
                'billing_day' => 1,
                'due_day' => 10,
                'bank_account_details' => null,
            ]
        );

        return Inertia::render('Tenant/Admin/Financial/Settings/Edit', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, string $community_slug)
    {
        $community = Community::where('slug', $community_slug)->firstOrFail();

        $validated = $request->validate([
            'base_budget' => ['required', 'numeric', 'min:0'],
            'late_fee_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'billing_day' => ['required', 'integer', 'min:1', 'max:31'],
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
            'bank_account_details' => ['nullable', 'array'],
            'bank_account_details.*.bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_details.*.account_type' => ['nullable', 'string', 'max:255'],
            'bank_account_details.*.account_number' => ['nullable', 'string', 'max:255'],
        ]);

        FinancialSetting::updateOrCreate(
            ['community_id' => $community->id],
            $validated
        );

        return redirect()->back()->with('success', 'Configuración financiera guardada exitosamente.');
    }
}
