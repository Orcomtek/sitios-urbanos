<?php

namespace App\Console\Commands;

use App\Models\BillingConcept;
use App\Models\Community;
use App\Models\Financial\Invoice;
use App\Models\FinancialSetting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('app:generate-monthly-invoices')]
#[Description('Generate recurring monthly administration invoices for communities')]
class GenerateMonthlyInvoicesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting invoice generation evaluation.");

        // Fetch ALL settings with their communities
        $settings = FinancialSetting::with('community')->get();

        if ($settings->isEmpty()) {
            $this->info('No financial settings configured.');

            return;
        }

        foreach ($settings as $setting) {
            $community = $setting->community;

            if (! $community || $community->status !== 'active') {
                continue;
            }

            $tz = $community->timezone ?? 'America/Bogota';
            $localTime = now($tz);

            // Execute only if today is the billing day AND it's exactly 1 AM local time
            if ($localTime->day !== $setting->billing_day || $localTime->hour !== 1) {
                continue;
            }

            $currentPeriod = $localTime->format('Y-m'); // e.g., '2026-06'
            $baseBudget = $setting->base_budget;

            // Calculate due date based on due_day
            // If due_day is less than billing_day, it likely means the due date is in the next month
            $dueDate = $localTime->day <= $setting->due_day
                ? $localTime->copy()->setDay($setting->due_day)
                : $localTime->copy()->addMonth()->setDay($setting->due_day);

            // Ensure billing concept 'recurring_hoa' exists
            $hoaConcept = BillingConcept::firstOrCreate([
                'community_id' => $community->id,
                'code' => 'recurring_hoa',
            ], [
                'name' => 'Cuota de Administración',
                'type' => 'recurring_hoa',
            ]);

            // Get current max invoice number (cast to int to avoid string sorting issues)
            $maxNumber = Invoice::where('community_id', $community->id)
                ->max(DB::raw('CAST(invoice_number AS INTEGER)')) ?? 0;

            $nextNumber = $maxNumber + 1;
            $invoicesCreated = 0;

            // Process Units in chunks
            $community->units()->chunk(100, function ($units) use (
                $community,
                $baseBudget,
                $currentPeriod,
                $hoaConcept,
                $dueDate,
                $localTime,
                &$nextNumber,
                &$invoicesCreated
            ) {
                DB::transaction(function () use (
                    $units, $community, $baseBudget, $currentPeriod, $hoaConcept, $dueDate, $localTime, &$nextNumber, &$invoicesCreated
                ) {
                    foreach ($units as $unit) {
                        // Idempotency Check
                        $exists = Invoice::where('community_id', $community->id)
                            ->where('unit_id', $unit->id)
                            ->where('billing_period', $currentPeriod)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        // Mathematical Engine: Round to 0 decimals for gateway compatibility
                        $total = round($baseBudget * $unit->coefficient, 0);

                        $invoiceNumber = (string) $nextNumber++;

                        // Create Invoice
                        $invoice = Invoice::create([
                            'community_id' => $community->id,
                            'unit_id' => $unit->id,
                            'user_id' => null, // Attached to unit, not temporary resident
                            'invoice_number' => $invoiceNumber,
                            'issue_date' => $localTime->toDateString(),
                            'due_date' => $dueDate->toDateString(),
                            'subtotal' => $total,
                            'total' => $total,
                            'status' => 'pending',
                            'billing_period' => $currentPeriod,
                        ]);

                        // Create Invoice Item
                        $invoice->items()->create([
                            'billing_concept_id' => $hoaConcept->id,
                            'description' => "Cuota de administración - {$currentPeriod}",
                            'quantity' => 1,
                            'unit_price' => $total,
                            'total' => $total,
                        ]);

                        $invoicesCreated++;
                    }
                });
            });

            $this->info("Community {$community->name} processed: {$invoicesCreated} invoices created.");
            Log::info('Monthly invoices generated', [
                'community_id' => $community->id,
                'period' => $currentPeriod,
                'count' => $invoicesCreated,
            ]);
        }

        $this->info('Invoice generation completed.');
    }
}
