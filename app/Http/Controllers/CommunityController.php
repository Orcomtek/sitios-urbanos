<?php

namespace App\Http\Controllers;

use App\Actions\GetUserCommunitiesAction;
use App\Actions\ResolveUserCommunityBySlugAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    /**
     * Display a listing of the user's communities.
     */
    public function index(Request $request, GetUserCommunitiesAction $action): Response|RedirectResponse
    {
        $communities = $action->execute($request->user());

        if ($communities->count() === 1) {
            $community = $communities->first();
            $baseUrl = config('app.central_domain');

            return redirect()->away('http://'.$community->slug.'.'.$baseUrl);
        }

        return Inertia::render('Communities/Index', [
            'communities' => $communities,
        ]);
    }

    /**
     * Handle the user explicitly entering a specific community.
     */
    public function enter(string $slug, Request $request, ResolveUserCommunityBySlugAction $resolver): RedirectResponse
    {
        $community = $resolver->execute($request->user(), $slug);

        $baseUrl = config('app.central_domain');

        return redirect()->away('http://'.$community->slug.'.'.$baseUrl);
    }
}
