@extends('pdf.layout')

@section('title', 'Certificado de Paz y Salvo')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-8">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight uppercase">Certificado de Paz y Salvo</h1>
        <div class="w-24 h-1 bg-indigo-600 mx-auto mt-4 mb-2"></div>
    </div>

    <div class="text-right mb-12">
        <p class="text-gray-700 font-medium">Fecha de expedición: {{ $date->format('d \d\e F \d\e Y') }}</p>
    </div>

    <div class="prose prose-lg text-gray-800 max-w-none text-justify leading-relaxed">
        <p>
            La Administración de <strong>{{ $unit->community->name ?? 'la Comunidad' }}</strong> certifica por medio del presente documento que:
        </p>

        <p class="my-6">
            @php $tipos = ['apartment'=>'Apartamento', 'house'=>'Casa', 'commercial'=>'Local', 'office'=>'Oficina', 'parking'=>'Parqueadero', 'storage'=>'Depósito']; $tipo = $tipos[$unit->property_type] ?? $unit->property_type; @endphp
            La unidad identificada con el número <strong>{{ $unit->identifier }}</strong>,
            cuyo tipo de propiedad es <strong>{{ $tipo }}</strong>,
            se encuentra a la fecha <strong>A PAZ Y SALVO</strong> por todo concepto de expensas comunes ordinarias, 
            extraordinarias, notas de débito y multas con esta Administración.
        </p>

        <p>
            Este certificado se expide a solicitud de la parte interesada y su validez está condicionada 
            a que no existan cheques devueltos o transacciones electrónicas pendientes de conciliación 
            en nuestros libros contables a la fecha de emisión.
        </p>
    </div>

    <div class="mt-24 text-center">
        <div class="w-64 border-b border-gray-800 mx-auto mb-2"></div>
        <p class="font-bold text-gray-900">La Administración</p>
        <p class="text-gray-600 text-sm mt-1">Sitios Urbanos - Plataforma de Gestión</p>
    </div>
</div>
@endsection
