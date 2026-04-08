<?php

use App\Models\User;
use App\Models\Community;
use App\Enums\CommunityRole;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('community.{communityId}', function (User $user, $communityId) {
    $community = Community::find($communityId);
    
    if (! $community) {
        return false;
    }

    return $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard);
});
