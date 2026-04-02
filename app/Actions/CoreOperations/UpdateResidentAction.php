<?php

namespace App\Actions\CoreOperations;

use App\Models\Community;
use App\Models\Resident;

class UpdateResidentAction
{
    public function execute(Community $community, Resident $resident, array $data): Resident
    {
        $resident->update($data);

        return $resident;
    }
}
