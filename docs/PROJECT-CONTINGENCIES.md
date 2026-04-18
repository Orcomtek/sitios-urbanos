# PROJECT CONTINGENCIES LEDGER
**Sitios Urbanos SaaS**
**Document Role:** Official tracking for UX refactoring, tech debt, and commercial pivots. Governed by RIGOR Methodology (v4.2).

---

## 🚨 Contingencia 1.1: Creación del MVP "Marketplace" (B2B2C)
**Estado:** `[Pendiente]`  
**Origen:** Pivote Comercial y Separación de Lógica.
**Diagnóstico:** El Marketplace no es un tablero comunitario, es un espacio publicitario premium gestionado por Sitios Urbanos. Actualmente carece de infraestructura propia.
**Alcance Técnico (MVP):**
- **Base de Datos:** Crear tabla `marketplace_businesses` (nombre, descripción, logo, whatsapp, categoría_id, community_id [nullable], status).
- **Gestión de Permisos:** El CRUD de creación/edición vivirá exclusivamente en el panel SuperAdmin de Sitios Urbanos. Las comunidades NO pueden crear negocios a menos que adquieran un *Feature Flag* premium.
- **Frontend (Residente):** Directorio de negocios locales aprobados para esa comunidad.
- **Interacción (CRO):** Botón CTA principal de "Contactar por WhatsApp" con mensaje pre-rellenado.
- **Fuera de Alcance:** Carrito de compras y pasarela de pagos interna (Diferido a V2).

---

## 🚨 Contingencia 1.2: Refactorización Legal y Privacidad P2P (Clasificados)
**Estado:** `[Pendiente]`  
**Origen:** Auditoría UX y Riesgo Legal.
**Diagnóstico:** La dependencia de la administración del conjunto para mediar en datos ocultos destruye la escalabilidad y no protege a la plataforma legalmente.
**Alcance Técnico:**
- **Legal Hard Gate:** Agregar checkbox obligatorio ("Acepto Términos y Condiciones") en la creación del clasificado. Validación estricta en Backend (422 si falta).
- **Refactorización de UX:** Eliminar flujos que deriven al residente a preguntar a la portería.
- **Privacidad Dinámica:** Si el vendedor oculta sus datos, la UI no muestra contacto, sino que renderiza un botón de "Solicitar datos de contacto" (Conecta con Contingencia 1.3).

---

## 🚨 Contingencia 1.3: Sistema de Mensajería Interna P2P (MVP Asíncrono)
**Estado:** `[Pendiente]`  
**Origen:** Continuidad del flujo de privacidad P2P.
**Diagnóstico:** Se requiere un mecanismo *Self-Service* para que los vecinos demuestren interés sin exponer sus datos iniciales ni salir de la plataforma.
**Alcance Técnico:**
- **Base de Datos:** Crear tabla `p2p_interactions` (listing_id, interested_resident_id, seller_resident_id, initial_message, status).
- **Flujo de Interés:** Modal de contacto para que el comprador envíe un mensaje inicial.
- **Gestión del Vendedor:** Panel interno en el detalle del producto con la lista de "Interesados", donde el vendedor puede leer y decidir si responde (intercambiando datos) o rechaza.
- **Fuera de Alcance:** Chat en tiempo real vía WebSockets. 

---

## 🚨 Contingencia 1.4: Refactorización de Proveedores (Categorías Dinámicas)
**Estado:** `[Pendiente]`  
**Origen:** Deuda Técnica (Enum Estático).
**Diagnóstico:** El Bloque 34 implementó categorías *hardcodeadas* (quemadas) en código. Es inaceptable para la escalabilidad del SaaS.
**Alcance Técnico:**
- **Base de Datos:** Eliminar el Enum de la tabla `providers` y establecer llave foránea `provider_category_id`.
- **Estandarización:** Crear tabla base para categorías gestionable desde el SuperAdmin.
- **Frontend:** Modificar formulario Admin y filtros Residente para consumir `/api/ecosystem/provider-categories` de forma reactiva.

---

## 🚨 Contingencia 2: Estandarización de Base de Datos (Taxonomías Globales y Locales)
**Estado:** `[Resuelta]`  
**Origen:** Auditoría Estructural (Estandarización de Selects y Categorías).
**Diagnóstico:** La proliferación de tablas individuales por cada select o categoría (ej. proveedores, marketplace, pqrs) es inescalable. Se requiere un diccionario centralizado.
**Alcance Técnico:**
- **Base de Datos:** Creación de la tabla `system_taxonomies` (Polimórfica/Grupo).
- **Columnas clave:** `id`, `type` (ej. provider_category), `community_id` (NULL = Global), `label`, `value`, `meta` (JSON para iconos/colores), `is_active`.
- **Query Scope:** Implementación de un *Scope* mixto (`GlobalOrTenantScope`) para que las consultas devuelvan los registros globales de Sitios Urbanos + los registros locales específicos de la comunidad activa.
- **Impacto Transversal:** Servirá como base para resolver la Contingencia 1.4 (Categorías de Proveedores) y futuros módulos.

*Fin del Registro Activo.*

