<?php

namespace App\Services\Privacy;

use App\Actions\Privacy\AnonymizeResidentAction;
use App\Models\Resident;
use App\Models\SecurityLog;

/**
 * Foundation for managing data retention and privacy compliance.
 *
 * This service implements baseline logic for cleaning old non-critical logs
 * and anonymizing soft-deleted records to fulfill privacy obligations without
 * broad destructive behavior.
 */
class RetentionManager
{
    public function __construct(
        private AnonymizeResidentAction $anonymizeResidentAction
    ) {}

    /**
     * Delete security logs older than the given number of months.
     * Note: Critical audit trails should rarely be deleted, but this
     * provides a foundation for compliant log rotation.
     */
    public function cleanOldLogs(int $months = 12 * 5): int
    {
        $cutoffDate = now()->subMonths($months);
        
        return SecurityLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Anonymize residents that were soft-deleted more than structurally allowed.
     */
    public function anonymizeDeletedResidents(int $months = 6): int
    {
        $cutoffDate = now()->subMonths($months);
        
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Resident> $residents */
        $residents = Resident::onlyTrashed()
            ->whereNotNull('email') // Simplistic check if it hasn't been anonymized
            ->where('deleted_at', '<', $cutoffDate)
            ->get();
            
        $count = 0;
        foreach ($residents as $resident) {
            $this->anonymizeResidentAction->execute($resident);
            $count++;
        }
        
        return $count;
    }
}
