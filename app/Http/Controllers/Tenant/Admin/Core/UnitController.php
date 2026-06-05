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
        $search = request('search');

        $units = $community->units()
            ->select('units.*')
            ->addSelect([
                'is_rented' => \Illuminate\Support\Facades\DB::table('community_user')
                    ->selectRaw('1')
                    ->whereColumn('community_user.unit_id', 'units.id')
                    ->where('community_user.community_id', $community->id)
                    ->whereIn('community_user.resident_role', ['tenant', 'inquilino'])
                    ->limit(1)
            ])
            ->with(['residents' => function($q) { 
                $q->where('is_active', true)->with(['familyMembers', 'vehicles', 'pets']); 
            }])
            ->withCount('residents')
            ->when($search, function ($query, $search) {
                $query->where('identifier', 'ilike', '%'.$search.'%');
            })
            ->orderBy('identifier')
            ->paginate(15)
            ->withQueryString();

        $units->getCollection()->transform(function ($unit) use ($community) {
            $taxonomies = \Illuminate\Support\Facades\Cache::get("taxonomies_tenant_{$community->id}") 
                ?? \App\Models\SystemTaxonomy::forCurrentTenantOrGlobal()->get(['type', 'label', 'value'])->groupBy('type')->toArray();
            
            $propertyTypes = collect($taxonomies['property_type'] ?? [])->keyBy('value');

            $sponsorPivots = \Illuminate\Support\Facades\DB::table('community_user')
                ->where('community_id', $community->id)
                ->where('unit_id', $unit->id)
                ->get()
                ->keyBy('user_id');

            foreach ($unit->residents as $resident) {
                $pivot = $sponsorPivots->get($resident->user_id);
                $role = $pivot->resident_role ?? $resident->resident_type->value;
                $resident->computed_role = $role;

                if (in_array($role, ['family', 'dependent'])) {
                    $sponsorId = $pivot->invited_by_user_id ?? null;
                    if ($sponsorId) {
                        $sponsorPivot = $sponsorPivots->get($sponsorId);
                        $sponsorRole = $sponsorPivot->resident_role ?? null;
                        if (!$sponsorRole) {
                            $sponsorResident = $unit->residents->firstWhere('user_id', $sponsorId);
                            $sponsorRole = $sponsorResident ? $sponsorResident->resident_type->value : null;
                        }

                        if (in_array($sponsorRole, ['owner', 'propietario'])) {
                            $resident->computed_role = 'family_owner';
                        } elseif (in_array($sponsorRole, ['tenant', 'inquilino'])) {
                            $resident->computed_role = 'family_tenant';
                        }
                    }
                }
            }

            $owner = $unit->residents->first(function ($resident) {
                return in_array($resident->computed_role, ['owner', 'propietario']);
            });

            $unit->owner = $owner ? [
                'name' => $owner->full_name,
                'phone' => $owner->phone,
                'email' => $owner->email,
            ] : null;

            $unit->type_label = $propertyTypes->get($unit->property_type)['label'] ?? $unit->property_type ?? 'No definido';
            
            $amenities = $unit->amenities ?? [];
            $unit->parking = $amenities['parking_description'] ?? (in_array('parking', $amenities) ? 'Parqueadero asignado' : null);
            $unit->storage = $amenities['storage_description'] ?? (in_array('storage', $amenities) ? 'Depósito asignado' : null);

            return $unit;
        });

        return Inertia::render('Tenant/Admin/Core/Units/Index', [
            'units' => $units,
            'filters' => request()->only(['search']),
        ]);
    }

    public function show(string $community_slug, Unit $unit)
    {
        $community = $this->context->require();

        $unit->load(['residents' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('full_name', 'asc');
        }]);

        $unit->is_rented = \Illuminate\Support\Facades\DB::table('community_user')
            ->where('community_id', $community->id)
            ->where('unit_id', $unit->id)
            ->whereIn('resident_role', ['tenant', 'inquilino'])
            ->exists();

        $sponsorPivots = \Illuminate\Support\Facades\DB::table('community_user')
            ->where('community_id', $community->id)
            ->where('unit_id', $unit->id)
            ->get()
            ->keyBy('user_id');

        foreach ($unit->residents as $resident) {
            $pivot = $sponsorPivots->get($resident->user_id);
            $role = $pivot->resident_role ?? $resident->resident_type->value;
            $resident->computed_role = $role;

            if (in_array($role, ['family', 'dependent'])) {
                $sponsorId = $pivot->invited_by_user_id ?? null;
                if ($sponsorId) {
                    $sponsorPivot = $sponsorPivots->get($sponsorId);
                    $sponsorRole = $sponsorPivot->resident_role ?? null;
                    if (!$sponsorRole) {
                        $sponsorResident = $unit->residents->firstWhere('user_id', $sponsorId);
                        $sponsorRole = $sponsorResident ? $sponsorResident->resident_type->value : null;
                    }

                    if (in_array($sponsorRole, ['owner', 'propietario'])) {
                        $resident->computed_role = 'family_owner';
                    } elseif (in_array($sponsorRole, ['tenant', 'inquilino'])) {
                        $resident->computed_role = 'family_tenant';
                    }
                }
            }
        }

        // Returning as JSON to be consumed asynchronously by the Vue Slide-over
        if (request()->wantsJson()) {
            return response()->json([
                'unit' => $unit,
            ]);
        }

        // Fallback for non-JSON requests
        return Inertia::render('Tenant/Admin/Core/Units/Show', [
            'unit' => $unit,
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
