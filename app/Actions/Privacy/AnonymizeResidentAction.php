<?php

namespace App\Actions\Privacy;

use App\Actions\Security\LogSecurityEventAction;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Str;

class AnonymizeResidentAction
{
    public function __construct(
        private LogSecurityEventAction $logSecurityEventAction
    ) {}

    public function execute(Resident $resident, ?User $actor = null): Resident
    {
        $hash = substr(md5($resident->id . $resident->created_at . Str::random()), 0, 8);
        
        $resident->full_name = "Anonymizado - {$hash}";
        $resident->email = null;
        $resident->phone = null;
        $resident->user_id = null;
        $resident->is_active = false;
        
        $resident->save();

        if ($resident->community) {
            $this->logSecurityEventAction->execute(
                $resident->community,
                $actor,
                'resident.anonymized',
                $resident,
                ['reason' => 'user requested anonymization']
            );
        }

        return $resident;
    }
}
