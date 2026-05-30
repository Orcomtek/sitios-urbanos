<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemTaxonomy;
use Illuminate\Http\Request;

class SystemTaxonomyController extends Controller
{
    /**
     * Display a listing of the taxonomies by type.
     */
    public function index(Request $request)
    {
        // Solución Arquitectónica: Extraemos el parámetro directamente por su nombre
        // para evitar que el {tenant} del subdominio desplace la variable.
        $type = $request->route('type');

        $taxonomies = SystemTaxonomy::forCurrentTenantOrGlobal()
            ->where('type', $type)
            ->get();

        // Override Rule: Local taxonomies override global ones with the same 'value'.
        $mergedTaxonomies = $taxonomies->sortBy(function ($taxonomy) {
            return $taxonomy->community_id === null ? 0 : 1;
        })->keyBy('value');

        return response()->json([
            'data' => $mergedTaxonomies->values()->all(),
        ]);
    }
}
