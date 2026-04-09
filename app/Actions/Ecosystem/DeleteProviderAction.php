<?php

namespace App\Actions\Ecosystem;

use App\Models\Provider;

class DeleteProviderAction
{
    public function execute(Provider $provider): bool
    {
        return $provider->delete();
    }
}
