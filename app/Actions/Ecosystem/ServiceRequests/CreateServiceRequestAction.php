<?php

namespace App\Actions\Ecosystem\ServiceRequests;

use App\Enums\ServiceRequestStatus;
use App\Enums\ServiceUrgency;
use App\Models\ProviderServiceRequest;
use App\Models\Resident;

class CreateServiceRequestAction
{
    /**
     * @param  array{provider_id: int, description: string, urgency: string, scheduled_date?: string|null}  $data
     */
    public function execute(Resident $resident, array $data): ProviderServiceRequest
    {
        return ProviderServiceRequest::create([
            'resident_id' => $resident->id,
            ...$data,
            'status' => ServiceRequestStatus::PENDING,
            'urgency' => ServiceUrgency::from($data['urgency']),
        ]);
    }
}
