<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Ecosystem;

use App\Enums\ListingCategory;
use App\Enums\ListingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['nullable', 'integer', 'min:0'],
            'category' => ['sometimes', Rule::enum(ListingCategory::class)],
            'status' => ['sometimes', Rule::enum(ListingStatus::class)],
            'show_contact_info' => ['boolean'],
        ];
    }
}
