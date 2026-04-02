<?php

namespace App\Http\Controllers\Tenant;

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

        $units = $community->units()
            ->orderBy('identifier')
            ->paginate(50);

        return Inertia::render('Tenant/Units/Index', [
            'units' => $units,
        ]);
    }

    public function create(string $community_slug): Response
    {
        // Require context just to ensure we are inside a tenant
        $this->context->require();

        return Inertia::render('Tenant/Units/Form', [
            'unit' => new Unit(),
        ]);
    }

    public function store(StoreUnitRequest $request, string $community_slug, CreateUnitAction $action): RedirectResponse
    {
        $action->execute($this->context->require(), $request->validated());

        return redirect()->route('units.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Unidad creada exitosamente.');
    }

    public function edit(string $community_slug, Unit $unit): Response
    {
        if ($unit->community_id !== $this->context->require()->id) {
            abort(404);
        }

        return Inertia::render('Tenant/Units/Form', [
            'unit' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, string $community_slug, Unit $unit, UpdateUnitAction $action): RedirectResponse
    {
        if ($unit->community_id !== $this->context->require()->id) {
            abort(404);
        }

        $action->execute($this->context->require(), $unit, $request->validated());

        return redirect()->route('units.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Unidad actualizada exitosamente.');
    }

    public function destroy(string $community_slug, Unit $unit, DeleteUnitAction $action): RedirectResponse
    {
        if ($unit->community_id !== $this->context->require()->id) {
            abort(404);
        }

        $action->execute($this->context->require(), $unit);

        return redirect()->route('units.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Unidad eliminada exitosamente.');
    }
}

