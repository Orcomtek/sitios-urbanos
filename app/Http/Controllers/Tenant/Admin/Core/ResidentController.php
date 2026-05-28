<?php

namespace App\Http\Controllers\Tenant\Admin\Core;

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

        return Inertia::render('Tenant/Admin/Core/Residents/Index', [
            'residents' => $residents,
        ]);
    }

    public function create(string $community_slug): Response
    {
        $community = $this->context->require();

        $units = $community->units()->orderBy('identifier')->get(['id', 'identifier']);

        return Inertia::render('Tenant/Admin/Core/Residents/Form', [
            'resident' => new Resident([
                'resident_type' => \App\Enums\ResidentType::TENANT,
                'is_active' => true,
                'pays_administration' => false,
            ]),
            'units' => $units,
        ]);
    }

    public function store(StoreResidentRequest $request, string $community_slug, \App\Services\ResidentOnboardingService $service): RedirectResponse
    {
        $community = $this->context->require();
        $unit = $community->units()->findOrFail($request->validated('unit_id'));
        
        $service->onboard($unit, $request->validated());

        return redirect()->route('tenant.admin.core.residents.index', ['community_slug' => $community->slug])
            ->with('success', 'Residente creado exitosamente.');
    }

    public function edit(string $community_slug, Resident $resident): Response
    {
        $community = $this->context->require();

        $units = $community->units()->orderBy('identifier')->get(['id', 'identifier']);

        return Inertia::render('Tenant/Admin/Core/Residents/Form', [
            'resident' => $resident,
            'units' => $units,
        ]);
    }

    public function update(UpdateResidentRequest $request, string $community_slug, Resident $resident, \App\Services\ResidentOnboardingService $service): RedirectResponse
    {
        $service->update($resident, $request->validated());

        return redirect()->route('tenant.admin.core.residents.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Residente actualizado exitosamente.');
    }

    public function destroy(string $community_slug, Resident $resident, DeleteResidentAction $action): RedirectResponse
    {
        $action->execute($this->context->require(), $resident);

        return redirect()->route('tenant.admin.core.residents.index', ['community_slug' => $this->context->require()->slug])
            ->with('success', 'Residente eliminado exitosamente.');
    }
}
