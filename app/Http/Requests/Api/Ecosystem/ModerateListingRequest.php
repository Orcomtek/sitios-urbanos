<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Ecosystem;

use App\Enums\ListingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModerateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ListingStatus::class)],
        ];
    }
}
