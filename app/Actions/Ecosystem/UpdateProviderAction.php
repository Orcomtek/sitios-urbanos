<?php

namespace App\Actions\Ecosystem;

use App\Models\Provider;

class UpdateProviderAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Provider $provider, array $data): Provider
    {
        $provider->update($data);

        return $provider->fresh();
    }
}
