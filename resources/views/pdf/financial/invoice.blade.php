@extends('pdf.layout')

@section('title', 'Factura ' . $invoice->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-start border-b pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Sitios Urbanos</h1>
            <p class="text-gray-500 mt-1">Facturación de Administración</p>
        </div>
        <div class="text-right">
            <h2 class="text-xl font-semibold text-gray-700">Factura #{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</h2>
            <p class="text-gray-500 mt-1">Fecha: {{ $invoice->created_at->format('d/m/Y') }}</p>
            @if($invoice->due_date)
            <p class="text-gray-500">Vencimiento: {{ $invoice->due_date->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>

    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Información de la Unidad</h3>
        <p><span class="font-medium text-gray-600">Unidad:</span> {{ $unit->identifier }}</p>
        <p><span class="font-medium text-gray-600">Comunidad:</span> {{ $unit->community->name ?? 'N/A' }}</p>
        @php $tipos = ['apartment'=>'Apartamento', 'house'=>'Casa', 'commercial'=>'Local', 'office'=>'Oficina', 'parking'=>'Parqueadero', 'storage'=>'Depósito']; $tipo = $tipos[$unit->property_type] ?? $unit->property_type; @endphp
        <p><span class="font-medium text-gray-600">Tipo:</span> {{ $tipo }}</p>
    </div>

    <table class="w-full text-left border-collapse mb-8">
        <thead>
            <tr class="bg-gray-100 border-b">
                <th class="py-3 px-4 font-semibold text-gray-700">Concepto</th>
                <th class="py-3 px-4 font-semibold text-gray-700 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr class="border-b">
                <td class="py-3 px-4">{{ $item->description }}</td>
                <td class="py-3 px-4 text-right">$ {{ number_format($item->amount ?? $item->total ?? $invoice->total, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="py-4 px-4 text-right font-bold text-gray-800 text-lg">TOTAL</td>
                <td class="py-4 px-4 text-right font-bold text-gray-800 text-lg">$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="mb-8 pt-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Pagos y Abonos Aplicados</h3>
        @php 
            $notasCredito = $invoice->unit->financialAdjustments ? $invoice->unit->financialAdjustments->where('type', 'credit') : collect();
            $hasPagos = ($invoice->payments && $invoice->payments->count() > 0) || $notasCredito->count() > 0;
        @endphp

        @if($hasPagos)
        <table class="w-full text-left border-collapse mb-4 text-sm">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="py-2 px-4 font-medium text-gray-600">Fecha</th>
                    <th class="py-2 px-4 font-medium text-gray-600">Método</th>
                    <th class="py-2 px-4 font-medium text-gray-600">Referencia</th>
                    <th class="py-2 px-4 font-medium text-gray-600 text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->payments)
                    @foreach($invoice->payments as $payment)
                    @php
                        $methodMap = [
                            'cash' => 'Efectivo',
                            'bank_transfer' => 'Transferencia Bancaria',
                            'check' => 'Cheque',
                            'pos_terminal' => 'Datáfono',
                            'internal_epayco' => 'Pago en Línea (ePayco)'
                        ];
                        $methodKey = $payment->method instanceof \BackedEnum ? $payment->method->value : $payment->method;
                        $methodName = $methodMap[$methodKey] ?? $methodKey ?? 'N/A';
                    @endphp
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ $payment->created_at->format('d/m/Y') }}</td>
                        <td class="py-2 px-4">{{ $methodName }}</td>
                        <td class="py-2 px-4">{{ $payment->external_reference ?? '-' }}</td>
                        <td class="py-2 px-4 text-right">$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @endif
                @foreach($notasCredito as $nota)
                <tr class="border-b">
                    <td class="py-2 px-4">{{ $nota->created_at->format('d/m/Y') }}</td>
                    <td class="py-2 px-4">Nota de Crédito / Abono</td>
                    <td class="py-2 px-4">{{ $nota->description ?? '-' }}</td>
                    <td class="py-2 px-4 text-right">$ {{ number_format($nota->amount, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-500 text-sm">No se han registrado pagos para esta factura.</p>
        @endif
        
        @php 
            $totalPagos = $invoice->payments ? $invoice->payments->sum('amount') : 0;
            $totalNotas = $notasCredito->sum('amount');
            $saldoPendiente = max(0, $invoice->total - $totalPagos - $totalNotas);
        @endphp
        <div class="flex justify-end mt-4">
            <div class="bg-gray-100 p-4 rounded-lg min-w-[250px]">
                <div class="flex justify-between font-bold text-gray-800 text-lg">
                    <span>Saldo a Pagar:</span>
                    <span>$ {{ number_format($saldoPendiente, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center text-sm text-gray-500 mt-12 pt-8 border-t">
        Este documento es generado electrónicamente por Sitios Urbanos.
    </div>
</div>
@endsection
