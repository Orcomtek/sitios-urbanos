<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Ecosystem;

use App\Enums\ListingCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['nullable', 'integer', 'min:0'],
            'category' => ['required', Rule::enum(ListingCategory::class)],
            'show_contact_info' => ['boolean'],
        ];
    }
}
