<?php

namespace App\Actions\CoreOperations;

use App\Models\Community;
use App\Models\Resident;

class DeleteResidentAction
{
    public function execute(Community $community, Resident $resident): void
    {
        $resident->delete();
    }
}
