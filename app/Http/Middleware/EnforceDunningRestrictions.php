<?php

namespace App\Http\Middleware;

use App\Enums\RestrictableModule;
use App\Models\FinancialSetting;
use App\Models\Resident;
use App\Services\Financial\DunningService;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces dunning restrictions on resident-facing routes.
 *
 * Usage (in routes):
 *   ->middleware('dunning:restrict_ecosystem')
 *
 * The middleware accepts the dunning flag key as a parameter (matching the
 * keys defined in RestrictableModule). If the user's unit is in arrears AND
 * the given restriction is enabled, a 403 is returned for JSON/API requests,
 * or a redirect to the financial statement for Inertia navigation requests.
 *
 * This middleware is fail-open:
 *  - If the user has no associated unit, no restriction applies.
 *  - If dunning is disabled for the community, no restriction applies.
 *  - If the module flag is not enabled, no restriction applies.
 */
class EnforceDunningRestrictions
{
    public function __construct(
        protected TenantContext $context,
        protected DunningService $dunningService,
    ) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleFlag): Response
    {
        $community = $this->context->get();

        if (! $community) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Resolve the resident's primary unit.
        $resident = Resident::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->with('unit')
            ->first();

        if (! $resident || ! $resident->unit) {
            // Fail-open: no unit found means no restriction.
            return $next($request);
        }

        $setting = FinancialSetting::where('community_id', $community->id)->first();

        if (! $setting || ! $setting->hasDunningEnabled()) {
            return $next($request);
        }

        $policies = $setting->getDunningPolicies();
        $flagEnabled = $policies['restrictions'][$moduleFlag] ?? false;

        if (! $flagEnabled) {
            return $next($request);
        }

        // Check actual arrears status (grace period applied inside DunningService).
        if (! $this->dunningService->isUnitInArrears($resident->unit, $setting)) {
            return $next($request);
        }

        // Unit is in arrears and this module is restricted.
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Tu unidad tiene obligaciones pendientes. Regulariza tu situación para acceder a este módulo.',
                'code' => 'DUNNING_RESTRICTED',
            ], Response::HTTP_FORBIDDEN);
        }

        // For Inertia navigation: redirect to financial statement for context.
        return redirect()->route('tenant.resident.financial.statement.index', [
            'community_slug' => $community->slug,
        ])->with('warning', 'Tu unidad tiene obligaciones pendientes. Regulariza tu situación para acceder a este módulo.');
    }
}
