<?php

namespace App\Http\Controllers\Tenant\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Models\BillingConcept;
use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillingConceptController extends Controller
{
    public function store(Request $request, string $community_slug)
    {
        $community = Community::where('slug', $community_slug)->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:fine,extraordinary,amenity_rental,discount'],
        ]);

        BillingConcept::create([
            'community_id' => $community->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'code' => Str::slug($validated['name']),
            'is_active' => true,
            'is_commissionable' => false,
        ]);

        return redirect()->back()->with('success', 'Concepto creado exitosamente.');
    }

    public function destroy(string $community_slug, BillingConcept $concept)
    {
        $community = Community::where('slug', $community_slug)->firstOrFail();

        if ($concept->community_id !== $community->id) {
            abort(403);
        }

        try {
            $concept->delete();
            return redirect()->back()->with('success', 'Concepto eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23503') { // Foreign Key Violation
                return redirect()->back()->withErrors(['error' => 'No se puede eliminar este concepto porque ya ha sido utilizado en transacciones o notas contables.']);
            }
            throw $e;
        }
    }
}
