<?php

namespace App\Actions\Ecosystem\ServiceRequests;

use App\Enums\ServiceRequestStatus;
use App\Models\ProviderServiceRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CancelServiceRequestAction
{
    public function execute(ProviderServiceRequest $serviceRequest): ProviderServiceRequest
    {
        if ($serviceRequest->status !== ServiceRequestStatus::PENDING) {
            throw new HttpException(422, 'Only pending service requests can be cancelled.');
        }

        $serviceRequest->update([
            'status' => ServiceRequestStatus::CANCELLED,
        ]);

        return $serviceRequest;
    }
}
