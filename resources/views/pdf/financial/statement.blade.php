@extends('pdf.layout')

@section('title', 'Estado de Cuenta ' . $unit->identifier)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-start border-b pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Sitios Urbanos</h1>
            <p class="text-gray-500 mt-1">ESTADO DE CUENTA OFICIAL</p>
        </div>
        <div class="text-right">
            <p class="text-gray-500 mt-1">Fecha: {{ $date->timezone(config('app.timezone'))->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Información de la Unidad</h3>
        <p><span class="font-medium text-gray-600">Unidad:</span> {{ $unit->identifier }}</p>
        <p><span class="font-medium text-gray-600">Comunidad:</span> {{ $unit->community->name ?? 'N/A' }}</p>
        
        <div class="mt-4 flex">
            <div class="bg-gray-100 p-4 rounded-lg min-w-[250px]">
                <div class="flex flex-col">
                    <span class="text-gray-600 font-medium">BALANCE NETO ACTUAL:</span>
                    <span class="font-bold text-gray-800 text-2xl mt-1">$ {{ number_format($netBalance, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8 pt-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Historial de Pagos Recientes</h3>
        
        @if($unit->payments && $unit->payments->count() > 0)
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
                @foreach($unit->payments as $payment)
                @php
                    $methodMap = [
                        'cash' => 'Efectivo',
                        'bank_transfer' => 'Transferencia Bancaria',
                        'check' => 'Cheque',
                        'pos_terminal' => 'Datáfono',
                        'internal_epayco' => 'Pago en Línea (ePayco)',
                        'manual_office' => 'Pago en Oficina'
                    ];
                    $methodKey = $payment->method instanceof \BackedEnum ? $payment->method->value : $payment->method;
                    $methodName = $methodMap[$methodKey] ?? $methodKey ?? 'N/A';
                @endphp
                <tr class="border-b">
                    <td class="py-2 px-4">{{ \Carbon\Carbon::parse($payment->created_at)->timezone(config('app.timezone'))->format('d/m/Y') }}</td>
                    <td class="py-2 px-4">{{ $methodName }}</td>
                    <td class="py-2 px-4">{{ $payment->external_reference ?? '-' }}</td>
                    <td class="py-2 px-4 text-right">$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-500 text-sm">No se han registrado pagos para esta unidad.</p>
        @endif
    </div>

    <div class="mb-8 pt-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Notas y Ajustes Contables</h3>
        
        @if($unit->financialAdjustments && $unit->financialAdjustments->count() > 0)
        <table class="w-full text-left border-collapse mb-4 text-sm">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="py-2 px-4 font-medium text-gray-600">Fecha</th>
                    <th class="py-2 px-4 font-medium text-gray-600">Tipo</th>
                    <th class="py-2 px-4 font-medium text-gray-600">Descripción</th>
                    <th class="py-2 px-4 font-medium text-gray-600 text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unit->financialAdjustments as $adj)
                @php
                    $typeMap = [
                        'credit' => 'Crédito (Abono)',
                        'debit' => 'Débito (Cargo)'
                    ];
                    $typeName = $typeMap[$adj->type] ?? $adj->type;
                @endphp
                <tr class="border-b">
                    <td class="py-2 px-4">{{ \Carbon\Carbon::parse($adj->created_at)->timezone(config('app.timezone'))->format('d/m/Y') }}</td>
                    <td class="py-2 px-4">{{ $typeName }}</td>
                    <td class="py-2 px-4">{{ $adj->description ?? '-' }}</td>
                    <td class="py-2 px-4 text-right">$ {{ number_format($adj->amount, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-500 text-sm">No hay notas ni ajustes contables registrados.</p>
        @endif
    </div>

    <div class="text-center text-sm text-gray-500 mt-12 pt-8 border-t">
        Este documento es un extracto oficial de cuenta generado por la plataforma Sitios Urbanos.
    </div>
</div>
@endsection
