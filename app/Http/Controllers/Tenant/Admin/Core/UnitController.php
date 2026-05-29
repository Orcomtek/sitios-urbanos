<?php

namespace App\Http\Controllers\Tenant\Admin\Core;

use App\Actions\CoreOperations\CreateUnitAction;
use App\Actions\CoreOperations\DeleteUnitAction;
use App\Actions\CoreOperations\UpdateUnitAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(string $community_slug): Response
    {
        $community = $this->context->require();

        // Optimized for the grid, no heavy eager loading, only counts if necessary
        $units = $community->units()
            ->withCount('residents')
            ->orderBy('identifier')
            ->paginate(50);

        return Inertia::render('Tenant/Admin/Core/Units/Index', [
            'units' => $units,
        ]);
    }

    public function show(string $community_slug, Unit $unit)
    {
        $this->context->require();

        // Eager load residents for the Slide-over context
        $unit->load(['residents' => function ($query) {
            $query->orderBy('is_active', 'desc')
                  ->orderBy('full_name', 'asc');
        }]);

        // Returning as JSON to be consumed asynchronously by the Vue Slide-over
        if (request()->wantsJson()) {
            return response()->json([
                'unit' => $unit
            ]);
        }

        // Fallback for non-JSON requests
        return Inertia::render('Tenant/Admin/Core/Units/Show', [
            'unit' => $unit
        ]);
    }

    public function create(string $community_slug): Response
    {
        // Require context just to ensure we are inside a tenant
        $this->context->require();

        return Inertia::render('Tenant/Admin/Core/Units/Form', [
            'unit' => new Unit,
        ]);
    }

    public function store(StoreUnitRequest $request, string $community_slug, CreateUnitAction $action): RedirectResponse
    {
        $action->execute($this->context->require(), $request->validated());

        return redirect()->route('tenant.admin.core.units.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Unidad creada exitosamente.');
    }

    public function edit(string $community_slug, Unit $unit): Response
    {
        return Inertia::render('Tenant/Admin/Core/Units/Form', [
            'unit' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, string $community_slug, Unit $unit, UpdateUnitAction $action): RedirectResponse
    {
        $action->execute($this->context->require(), $unit, $request->validated());

        return redirect()->route('tenant.admin.core.units.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Unidad actualizada exitosamente.');
    }

    public function destroy(string $community_slug, Unit $unit, DeleteUnitAction $action): RedirectResponse
    {
        $action->execute($this->context->require(), $unit);

        return redirect()->route('tenant.admin.core.units.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Unidad eliminada exitosamente.');
    }
}
