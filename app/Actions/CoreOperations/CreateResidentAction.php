<?php

namespace App\Actions\CoreOperations;

use App\Models\Community;
use App\Models\Resident;

class CreateResidentAction
{
    public function execute(Community $community, array $data): Resident
    {
        return $community->residents()->create($data);
    }
}
