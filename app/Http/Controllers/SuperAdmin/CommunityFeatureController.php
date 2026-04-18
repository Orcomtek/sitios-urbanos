<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityFeatureController extends Controller
{
    /**
     * Update the specified community's SaaS features.
     */
    public function update(Request $request, Community $community): JsonResponse
    {
        $validated = $request->validate([
            'feature' => ['required', 'string'],
            'enabled' => ['required', 'boolean'],
        ]);

        $settings = $community->saas_settings ?? [];
        $settings[$validated['feature']] = $validated['enabled'];

        $community->update([
            'saas_settings' => $settings,
        ]);

        return response()->json([
            'message' => 'Feature settings updated successfully.',
            'saas_settings' => $community->saas_settings,
        ]);
    }
}
