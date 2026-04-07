<?php

namespace App\Actions\CoreOperations;

use App\Actions\Security\LogSecurityEventAction;
use App\Models\Community;
use App\Models\Resident;
use Illuminate\Support\Facades\Auth;

class DeleteResidentAction
{
    public function __construct(
        private LogSecurityEventAction $logSecurityEventAction
    ) {}

    public function execute(Community $community, Resident $resident): void
    {
        $resident->delete();

        $this->logSecurityEventAction->execute(
            $community,
            Auth::user(),
            'resident.deleted',
            $resident,
            ['reason' => 'user deleted resident']
        );
    }
}
