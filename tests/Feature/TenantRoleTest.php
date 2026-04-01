<?php

namespace Tests\Feature;

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_different_roles_in_different_communities()
    {
        $user = User::factory()->create();

        $communityAdmin = Community::factory()->create();
        $communityResident = Community::factory()->create();

        $user->communities()->attach($communityAdmin->id, ['role' => CommunityRole::Admin]);
        $user->communities()->attach($communityResident->id, ['role' => CommunityRole::Resident]);

        $this->assertEquals(CommunityRole::Admin, $user->roleInCommunity($communityAdmin));
        $this->assertEquals(CommunityRole::Resident, $user->roleInCommunity($communityResident));
    }

    public function test_has_role_in_community_method_works_correctly()
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $user->communities()->attach($community->id, ['role' => CommunityRole::Guard]);

        $this->assertTrue($user->hasRoleInCommunity($community, CommunityRole::Guard));
        $this->assertTrue($user->hasRoleInCommunity($community, 'guard'));
        $this->assertFalse($user->hasRoleInCommunity($community, CommunityRole::Admin));
        $this->assertFalse($user->hasRoleInCommunity($community, 'admin', 'resident'));
        $this->assertTrue($user->hasRoleInCommunity($community, 'admin', 'guard'));
    }

    public function test_role_is_returned_null_if_user_not_in_community()
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $this->assertNull($user->roleInCommunity($community));
        $this->assertFalse($user->hasRoleInCommunity($community, CommunityRole::Resident));
    }
}
