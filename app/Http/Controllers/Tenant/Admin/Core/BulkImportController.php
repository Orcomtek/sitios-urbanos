<?php

namespace App\Http\Controllers\Tenant\Admin\Core;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBulkImportJob;
use App\Models\BulkImport;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class BulkImportController extends Controller
{
    /**
     * Display the bulk import page.
     */
    public function index()
    {
        return \Inertia\Inertia::render('Tenant/Admin/Core/Imports/Index');
    }

    /**
     * Store the uploaded CSV file and dispatch the import job.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // Max 10MB
            'type' => ['required', 'string', 'in:residents,balances,units'],
        ]);

        $community = app(TenantContext::class)->get();
        $file = $request->file('file');

        $path = $file->store('imports', 'local');

        $import = BulkImport::create([
            'community_id' => $community->id,
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'status' => 'pending',
            'file_path' => $path,
            'total_rows' => 0,
            'processed_rows' => 0,
            'failed_rows' => 0,
            'errors' => [],
        ]);

        ProcessBulkImportJob::dispatch($import);

        return response()->json([
            'message' => 'Import started successfully.',
            'import' => $import,
        ]);
    }

    /**
     * Display the current status of the bulk import for polling.
     */
    public function show(string $communitySlug, BulkImport $import)
    {
        // Ensure the import belongs to the active tenant
        $community = app(TenantContext::class)->get();
        if ($import->community_id !== $community->id) {
            abort(404);
        }

        return response()->json([
            'import' => $import,
        ]);
    }
}
