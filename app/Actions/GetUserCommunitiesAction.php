<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetUserCommunitiesAction
{
    /**
     * Retrieve all active communities assigned to the given user.
     */
    public function execute(User $user): Collection
    {
        return $user->communities()->where('status', 'active')->get();
    }
}
