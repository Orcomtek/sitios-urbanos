<?php

namespace App\Actions\Ecosystem;

use App\Models\Provider;

class RegisterProviderAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Provider
    {
        return Provider::create($data);
    }
}
