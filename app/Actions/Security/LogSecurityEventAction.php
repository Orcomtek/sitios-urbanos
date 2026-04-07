<?php

namespace App\Actions\Security;

use App\Models\Community;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class LogSecurityEventAction
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function execute(
        Community $community,
        ?User $actor,
        string $action,
        ?Model $subject = null,
        array $details = []
    ): SecurityLog {
        $log = new SecurityLog;
        $log->community_id = $community->id;
        $log->actor_id = $actor?->id;
        $log->action = $action;

        if ($subject) {
            $log->subject()->associate($subject);
        }

        $log->details = empty($details) ? null : $details;
        $log->ip_address = Request::ip();
        $log->user_agent = Request::userAgent();

        $log->save();

        return $log;
    }
}
