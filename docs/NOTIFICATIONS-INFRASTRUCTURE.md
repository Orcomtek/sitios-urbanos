# 📡 Infraestructura Omnicanal de Notificaciones (RIGOR)

Este documento define la arquitectura, las reglas de negocio y los proveedores tecnológicos para el motor de notificaciones de "Sitios Urbanos". El objetivo es garantizar la entregabilidad crítica (Botón de Pánico, Accesos) manteniendo los costos operativos controlados bajo el modelo de suscripción SaaS.

## 1. Arquitectura de Procesamiento (Async-First)
Para proteger el tiempo de respuesta del servidor HTTP (UX/CRO), **ninguna notificación externa (SMS, WhatsApp, Email, Push) se procesará de forma síncrona**.
* **Motor de Colas:** Se utilizará Redis (o AWS SQS en producción) mediante el sistema de colas nativo de Laravel (`ShouldQueue`).
* **WebSockets (In-App):** Se utilizará Laravel Reverb para la transmisión en tiempo real a los clientes conectados (dashboard y app).

## 2. Reglas de Negocio y Control de Costos (Fair Use)
El envío indiscriminado de SMS/WhatsApp destruye el margen de rentabilidad del SaaS. Se implementan las siguientes barreras:

* **Cuota Regular:** Cada Unidad tiene derecho a **3 SMS por mes** para notificaciones operativas (ej. Código QR de invitado, llegada de paquete crítico).
* **Fallback Cascade:** Si la cuota de SMS se agota, el sistema degradará automáticamente el envío a canales gratuitos: `Push Notification -> Email -> In-App`.
* **Emergency Pool (Fondo de Emergencia):** El evento `panic.triggered` (Botón de Pánico) **ignora** las cuotas regulares. Utiliza un fondo de saldo global protegido exclusivamente para garantizar que la alerta llegue a la portería y a los co-residentes, sin importar el consumo mensual de la unidad.

## 3. Esquema de Base de Datos (Auditoría e Inbox)
El ecosistema depende de tres tablas centrales para auditar y mostrar las notificaciones:

1. `sms_quotas`:
   * Controla el ciclo de facturación y el contador de SMS consumidos por `unit_id`.
2. `notification_events` (Log de Auditoría):
   * Registro inmutable de cada disparo hacia integradores externos (AWS/Twilio/Meta).
   * Columnas clave: `id`, `community_id`, `event_type`, `channel`, `payload`, `status` (sent, failed, delivered), `provider_response`.
3. `user_notifications` (Bandeja In-App):
   * Alimenta el "Centro de Notificaciones" de la interfaz de usuario (Vue/Inertia).
   * Columnas clave: `id`, `user_id`, `type`, `data` (JSON), `read_at`.

## 4. Proveedores Tecnológicos (Channels)
* **SMS:** AWS SNS (Amazon Simple Notification Service). Priorizado por integración directa con la infraestructura en la nube y costos por volumen.
* **WhatsApp:** Meta Cloud API (Canal oficial) para envío de plantillas pre-aprobadas (ej. Códigos de Acceso).
* **Emails:** Resend o Amazon SES.
* **In-App / Push:** Laravel Reverb (WebSockets) + Navegador Web Push API.

## 5. Registro de Eventos (Event Mapping)
| Evento Core | Canal Primario | Fallback 1 | Fallback 2 | Ignora Cuota |
| :--- | :--- | :--- | :--- | :--- |
| `panic.triggered` | In-App (Alarma) | SMS | WhatsApp | **SÍ** |
| `access.qr_generated` | WhatsApp | SMS | Email | NO |
| `parcel.received` | In-App (Push) | Email | - | NO |
| `governance.poll_opened` | Email | In-App | - | NO |