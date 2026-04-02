<?php

namespace App\Actions\CoreOperations;

use App\Models\Community;
use App\Models\Unit;
use LogicException;

class DeleteUnitAction
{
    public function execute(Community $community, Unit $unit): void
    {
        if ($unit->community_id !== $community->id) {
            throw new LogicException('Unit does not belong to the active community context.');
        }

        $unit->delete();
    }
}
