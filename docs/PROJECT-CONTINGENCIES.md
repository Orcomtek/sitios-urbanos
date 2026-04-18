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
**Estado:** `[Resuelta]`  
**Origen:** Deuda Técnica (Enum Estático).
**Diagnóstico:** El Bloque 34 implementó categorías *hardcodeadas* (quemadas) en código. Es inaceptable para la escalabilidad del SaaS.
**Alcance Técnico:**
- **Base de Datos:** Eliminar el Enum de la tabla `providers` y establecer llave foránea `provider_category_id`.
- **Estandarización:** Crear tabla base para categorías gestionable desde el SuperAdmin.
- **Frontend:** Modificar formulario Admin y filtros Residente para consumir `/api/ecosystem/provider-categories` de forma reactiva.

### [Resolved] Contingency 1.4: Dynamic Provider Categories (Frontend Integration)
* **Objective:** Refactor Admin and Resident Provider views to consume dynamic system taxonomies instead of hardcoded arrays.
* **Execution details:**
  * Removed static category arrays from `Admin/Providers/Index.vue` and `Resident/Providers/Index.vue`.
  * Implemented asynchronous data fetching via `axios` on the `onMounted` hook.
  * Added graceful loading states to prevent form submission before data is ready.
  * **Validation:** Verified end-to-end that the "Override Rule" and "Tenant Isolation" correctly reflect in the UI for both roles.

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

### [Resolved] Contingency 2: Database Standardization (Global/Local Taxonomies)
* **Objective:** Centralize all dynamic select options and categories into a single polymorphic table to prevent database fragmentation.
* **Execution details:**
  * Created `system_taxonomies` migration with `foreignId` for `community_id` (nullable for global records).
  * Implemented `SystemTaxonomy` model bypassing standard `TenantScoped` to allow a custom `scopeForCurrentTenantOrGlobal`.
  * Created `SystemTaxonomyController` mapped to `/api/system/taxonomies/{type}`. 
  * **Critical Bug Fix:** Forced `$request->route('type')` in the controller to prevent the tenant subdomain string from shifting and overwriting the `$type` parameter.
* **Frozen Architectural Decision (The Override Rule):** If a local taxonomy (created by a community) shares the exact same `value` as a global Sitios Urbanos taxonomy, the Controller uses `.keyBy('value')` to ensure the local record natively overwrites the global one in the API response, preventing duplicate keys in Vue loops.

## 🚨 Contingencia 3: Separación de Poderes y Parametrización Global (El Panel SuperAdmin de Sitios Urbanos vs. Panel Local).

**Estado:** `[Resuelta]` 

### [Resolved] Contingency 3: Separation of Powers & Global Parameterization (SaaS Authorization Engine)
* **Objective:** Enforce the "Default-Closed" rule for premium SaaS modules to prevent unauthorized access by local community admins.
* **Execution details:**
  * Added `saas_settings` JSONB column to `communities` table for high-performance feature flagging without N+1 queries.
  * Created `system_settings` table for SuperAdmin global parameterization (e.g., automated moderation dictionaries).
  * Implemented `EnsureTenantHasFeature` middleware and registered alias `tenant.feature` in `bootstrap/app.php` (Laravel 13 standard).
  * Created `CommunityFeatureController` isolated for SuperAdmin use.
* **Validation:** Verified via Tinker that a tenant explicitly fails-closed (403) without a feature and succeeds when granted the flag via JSONB update.


*Fin del Registro Activo.*