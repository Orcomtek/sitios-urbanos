<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Financial\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-'.fake()->unique()->numerify('######'),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => fake()->numberBetween(10000, 500000),
            'total' => fn (array $attributes) => $attributes['subtotal'],
            'status' => InvoiceStatus::PENDING,
            'billing_period' => now()->subMonths(fake()->unique()->numberBetween(0, 60))->format('Y-m'),
        ];
    }
}
