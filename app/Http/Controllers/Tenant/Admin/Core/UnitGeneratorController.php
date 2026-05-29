<?php

namespace App\Http\Controllers\Tenant\Admin\Core;

use App\Actions\CoreOperations\BulkUpdateUnitAmenitiesAction;
use App\Actions\CoreOperations\GenerateTopologicalMatrixAction;
use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitGeneratorController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(string $community_slug): Response
    {
        $community = $this->context->require();
        
        $units = $community->units()
            ->orderBy('identifier')
            ->get(['id', 'identifier', 'amenities', 'status', 'property_type']);

        return Inertia::render('Tenant/Admin/Core/Units/Generator', [
            'units' => $units
        ]);
    }

    public function generate(Request $request, string $community_slug, GenerateTopologicalMatrixAction $action): RedirectResponse
    {
        $community = $this->context->require();

        $validated = $request->validate([
            'blocks' => ['required', 'integer', 'min:1'],
            'floors' => ['required', 'integer', 'min:1'],
            'units' => ['required', 'integer', 'min:1'],
            'pattern' => ['required', 'string'],
            'property_type' => ['nullable', 'string'],
        ]);

        $action->execute($community, $validated);

        return back()->with('success', 'Matriz generada exitosamente.');
    }

    public function bulkUpdateAmenities(Request $request, string $community_slug, BulkUpdateUnitAmenitiesAction $action): RedirectResponse
    {
        $community = $this->context->require();

        $validated = $request->validate([
            'unit_ids' => ['required', 'array'],
            'unit_ids.*' => ['integer', 'exists:units,id'],
            'amenities' => ['required', 'array'],
            'amenities.*' => ['string'],
        ]);

        $action->execute($community, $validated['unit_ids'], $validated['amenities']);

        return back()->with('success', 'Amenidades actualizadas masivamente.');
    }
}
