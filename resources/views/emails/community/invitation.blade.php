<x-mail::message>
# Hola,

Has sido invitado(a) a unirte a la comunidad **{{ $communityName }}** en Sitios Urbanos.

Para acceder, por favor haz clic en el siguiente botón:

<x-mail::button :url="$acceptUrl">
Aceptar Invitación
</x-mail::button>

Si no esperabas esta invitación, puedes ignorar este correo.

Gracias,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
