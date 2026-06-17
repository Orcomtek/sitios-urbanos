<?php

namespace App\Services\Financial;

use App\Actions\Financial\CalculateUnitBalanceAction;
use App\Models\Financial\Invoice;
use App\Models\Unit;
use Exception;
use Spatie\Browsershot\Browsershot;

class BrowsershotPdfService
{
    protected CalculateUnitBalanceAction $calculateBalanceAction;

    public function __construct(CalculateUnitBalanceAction $calculateBalanceAction)
    {
        $this->calculateBalanceAction = $calculateBalanceAction;
    }

    /**
     * Generate an Invoice PDF.
     *
     * @return string The raw PDF binary string
     */
    public function generateInvoicePdf(Invoice $invoice): string
    {
        $invoice->load(['unit.community', 'items']);

        $html = view('pdf.financial.invoice', [
            'invoice' => $invoice,
            'unit' => $invoice->unit,
        ])->render();

        return Browsershot::html($html)
            ->setNodeBinary(env('NODE_BINARY_PATH', '/usr/local/bin/node'))
            ->setNpmBinary(env('NPM_BINARY_PATH', '/usr/local/bin/npm'))
            ->format('Letter')
            ->margins(10, 10, 10, 10)
            ->pdf();
    }

    /**
     * Generate a 'Paz y Salvo' Certificate PDF.
     *
     * @return string The raw PDF binary string
     *
     * @throws Exception
     */
    public function generatePazYSalvoPdf(Unit $unit): string
    {
        $unit->load('community');

        $balance = $this->calculateBalanceAction->execute($unit);

        if ($balance > 0) {
            throw new Exception("La cuenta presenta saldo en mora (Balance: {$balance}). No se puede generar el certificado de Paz y Salvo.");
        }

        $html = view('pdf.financial.paz-y-salvo', [
            'unit' => $unit,
            'date' => now(),
        ])->render();

        return Browsershot::html($html)
            ->setNodeBinary(env('NODE_BINARY_PATH', '/usr/local/bin/node'))
            ->setNpmBinary(env('NPM_BINARY_PATH', '/usr/local/bin/npm'))
            ->format('Letter')
            ->margins(10, 10, 10, 10)
            ->pdf();
    }
}
