<?php

namespace App\Actions\CoreOperations;

use App\Models\Community;
use App\Models\Unit;

class CreateUnitAction
{
    public function execute(Community $community, array $data): Unit
    {
        return $community->units()->create($data);
    }
}
