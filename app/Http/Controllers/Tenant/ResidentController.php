<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CoreOperations\CreateResidentAction;
use App\Actions\CoreOperations\DeleteResidentAction;
use App\Actions\CoreOperations\UpdateResidentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ResidentController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(string $community_slug): Response
    {
        $community = $this->context->require();

        $residents = $community->residents()
            ->with('unit')
            ->orderBy('id', 'desc')
            ->paginate(50);

        return Inertia::render('Tenant/Residents/Index', [
            'residents' => $residents,
        ]);
    }

    public function create(string $community_slug): Response
    {
        $community = $this->context->require();

        $units = $community->units()->orderBy('identifier')->get(['id', 'identifier']);

        return Inertia::render('Tenant/Residents/Form', [
            'resident' => new Resident([
                'type' => 'tenant',
                'status' => 'active',
            ]),
            'units' => $units,
        ]);
    }

    public function store(StoreResidentRequest $request, string $community_slug, CreateResidentAction $action): RedirectResponse
    {
        $action->execute($this->context->require(), $request->validated());

        return redirect()->route('residents.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Residente creado exitosamente.');
    }

    public function edit(string $community_slug, Resident $resident): Response
    {
        $community = $this->context->require();

        if ($resident->community_id !== $community->id) {
            abort(404);
        }

        $units = $community->units()->orderBy('identifier')->get(['id', 'identifier']);

        return Inertia::render('Tenant/Residents/Form', [
            'resident' => $resident,
            'units' => $units,
        ]);
    }

    public function update(UpdateResidentRequest $request, string $community_slug, Resident $resident, UpdateResidentAction $action): RedirectResponse
    {
        if ($resident->community_id !== $this->context->require()->id) {
            abort(404);
        }

        $action->execute($this->context->require(), $resident, $request->validated());

        return redirect()->route('residents.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Residente actualizado exitosamente.');
    }

    public function destroy(string $community_slug, Resident $resident, DeleteResidentAction $action): RedirectResponse
    {
        if ($resident->community_id !== $this->context->require()->id) {
            abort(404);
        }

        $action->execute($this->context->require(), $resident);

        return redirect()->route('residents.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Residente eliminado exitosamente.');
    }
}
