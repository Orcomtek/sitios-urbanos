<?php

namespace App\Http\Requests\Ecosystem;

use App\Enums\ProviderStatus;
use App\Enums\ServiceUrgency;
use App\Models\Provider;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderServiceRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) {
                    $provider = Provider::find($value);

                    if (! $provider) {
                        $fail('The selected provider does not exist.');

                        return;
                    }

                    if ($provider->status !== ProviderStatus::ACTIVE) {
                        $fail('The selected provider is not currently active.');
                    }
                },
            ],
            'description' => ['required', 'string', 'max:5000'],
            'urgency' => ['required', 'string', Rule::enum(ServiceUrgency::class)],
            'scheduled_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
