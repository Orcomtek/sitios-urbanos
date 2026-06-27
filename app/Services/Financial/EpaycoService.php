<?php

namespace App\Services\Financial;

class EpaycoService
{
    /**
     * Generate ePayco signature.
     */
    public function generateSignature(array $data): string
    {
        $p_cust_id_cliente = config('services.epayco.p_cust_id_cliente');
        $p_key = config('services.epayco.p_key');

        $x_ref_payco = $data['x_ref_payco'] ?? '';
        $x_transaction_id = $data['x_transaction_id'] ?? '';
        $x_amount = $data['x_amount'] ?? '';
        $x_currency_code = $data['x_currency_code'] ?? '';

        $signatureString = "{$p_cust_id_cliente}^{$p_key}^{$x_ref_payco}^{$x_transaction_id}^{$x_amount}^{$x_currency_code}";

        return hash('sha256', $signatureString);
    }
}
