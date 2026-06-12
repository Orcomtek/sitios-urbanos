<?php

namespace App\Http\Controllers\Tenant\Admin\Core;

use App\Actions\CoreOperations\DeleteResidentAction;
use App\Enums\ResidentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Mail\CommunityInvitationMail;
use App\Models\Resident;
use App\Models\UserInvitation;
use App\Services\ResidentOnboardingService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ResidentController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(string $community_slug): Response
    {
        $community = $this->context->require();
        $search = request('search');
        $status = request('status', 'active');

        $residents = $community->residents()
            ->with('unit')
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'ilike', '%'.$search.'%')
                        ->orWhere('email', 'ilike', '%'.$search.'%')
                        ->orWhere('phone', 'ilike', '%'.$search.'%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $units = $community->units()->orderBy('identifier')->get(['id', 'identifier']);

        return Inertia::render('Tenant/Admin/Core/Residents/Index', [
            'residents' => $residents,
            'filters' => request()->only(['search', 'status']),
            'units' => $units,
        ]);
    }

    public function show(string $community_slug, Resident $resident)
    {
        $this->context->require();

        if (request()->wantsJson()) {
            return response()->json([
                'resident' => $resident,
            ]);
        }
        abort(404);
    }

    public function create(string $community_slug): Response
    {
        $community = $this->context->require();

        $units = $community->units()->orderBy('identifier')->get(['id', 'identifier']);

        return Inertia::render('Tenant/Admin/Core/Residents/Form', [
            'resident' => new Resident([
                'resident_type' => ResidentType::TENANT,
                'is_active' => true,
                'pays_administration' => false,
            ]),
            'units' => $units,
        ]);
    }

    public function store(StoreResidentRequest $request, string $community_slug, ResidentOnboardingService $service): RedirectResponse
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

    public function update(UpdateResidentRequest $request, string $community_slug, Resident $resident, ResidentOnboardingService $service): RedirectResponse
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

    public function dispatchInvitations(string $community_slug): RedirectResponse
    {
        $community = $this->context->require();

        $residents = $community->residents()
            ->whereNull('user_id')
            ->whereNotNull('email')
            ->get();

        $count = 0;
        foreach ($residents as $resident) {
            $invitation = UserInvitation::updateOrCreate(
                [
                    'email' => $resident->email,
                    'community_id' => $community->id,
                ],
                [
                    'token' => UserInvitation::generateSecureToken(),
                    'role' => $resident->resident_type->value === 'owner' ? 'resident' : 'resident', // Assuming resident by default
                    'unit_id' => $resident->unit_id,
                    'invited_by' => auth()->id(),
                    'expires_at' => now()->addDays(7),
                    'status' => 'pending',
                ]
            );

            Mail::to($resident->email)
                ->queue(new CommunityInvitationMail($invitation));

            $count++;
        }

        return redirect()->back()->with('success', "Se han encolado {$count} invitaciones.");
    }
}
