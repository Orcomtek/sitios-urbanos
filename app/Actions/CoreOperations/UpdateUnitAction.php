<?php

namespace App\Actions\CoreOperations;

use App\Models\Community;
use App\Models\Unit;
use LogicException;

class UpdateUnitAction
{
    public function execute(Community $community, Unit $unit, array $data): Unit
    {
        if ($unit->community_id !== $community->id) {
            throw new LogicException('Unit does not belong to the active community context.');
        }

        $unit->update($data);

        return $unit->fresh();
    }
}
