<?php

namespace App\Actions;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResolveUserCommunityBySlugAction
{
    /**
     * Resolves a user's community explicitly by slug, ensuring the community is active.
     * Throws ModelNotFoundException unconditionally on failure.
     *
     * @throws ModelNotFoundException
     */
    public function execute(User $user, string $slug): Community
    {
        return $user->communities()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();
    }
}
