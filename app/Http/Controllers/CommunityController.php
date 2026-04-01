<?php

namespace App\Http\Controllers;

use App\Actions\GetUserCommunitiesAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    /**
     * Display a listing of the user's communities.
     */
    public function index(Request $request, GetUserCommunitiesAction $action): Response
    {
        $communities = $action->execute($request->user());

        return Inertia::render('Communities/Index', [
            'communities' => $communities,
        ]);
    }
}
