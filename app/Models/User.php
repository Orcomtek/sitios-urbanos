<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\CommunityRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The communities that the user belongs to.
     *
     * @return BelongsToMany<Community, $this>
     */
    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class)
            ->withPivot(['role', 'unit_id']);
    }

    /**
     * Get the user's role in a specific community.
     */
    public function roleInCommunity(Community $community): ?CommunityRole
    {
        $role = $this->communities
            ->firstWhere('id', $community->id)
            ?->pivot
            ?->role;

        return $role ? CommunityRole::tryFrom($role) : null;
    }

    /**
     * Check if the user has a specific role (or one of the roles) in a community.
     */
    public function hasRoleInCommunity(Community $community, CommunityRole|string ...$roles): bool
    {
        $currentRole = $this->roleInCommunity($community);

        if (! $currentRole) {
            return false;
        }

        $rolesToMatch = array_map(
            fn ($r) => $r instanceof CommunityRole ? $r : CommunityRole::tryFrom($r),
            $roles
        );

        return in_array($currentRole, array_filter($rolesToMatch), true);
    }
}
