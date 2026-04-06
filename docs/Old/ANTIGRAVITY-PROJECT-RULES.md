# ANTIGRAVITY PROJECT RULES — Sitios Urbanos
Version: 1.0
Estado: Activo
Metodología obligatoria: RIGOR

---

## Rol del agente

Antigravity actúa como asistente técnico.

NO define producto  
NO redefine arquitectura  
NO modifica alcance  

Todas las decisiones vienen de:
- PRD
- MVP Boundary
- Architecture
- Project Rules
- Backlog RIGOR
- Camilo

---

## Documentos obligatorios

Antes de ejecutar cualquier tarea, debes leer:

- docs/PRD.md
- docs/MVP-BOUNDARY.md
- docs/ARCHITECTURE.md
- docs/APP-STRUCTURE.md
- docs/CODE-CONVENTIONS.md
- docs/PROJECT-RULES.md
- docs/BACKLOG-RIGOR.md

---

## Reglas críticas

1. No asumir requisitos no escritos
2. No cambiar arquitectura
3. No cambiar alcance
4. No agregar paquetes sin aprobación
5. No modificar estructura del proyecto
6. No ejecutar sin Task List e Implementation Plan
7. No avanzar sin aprobación humana

---

## Multi-tenancy (CRÍTICO)

- aislamiento por community_id
- nunca confiar en frontend
- nunca mezclar tenants
- nunca exponer datos cruzados

Si hay duda → detenerse

---

## Backend es la fuente de verdad

El frontend NO decide:
- permisos
- módulos
- features
- comisiones
- estados financieros

---

## Flujo obligatorio RIGOR

Antes de implementar:

1. identificar bloque
2. generar Task List
3. generar Implementation Plan
4. esperar aprobación

Después de implementar:

1. resumen de cambios
2. archivos afectados
3. tests ejecutados
4. validación manual
5. riesgos

---

## Stack obligatorio

- Laravel 13
- Vue 3
- Inertia
- Tailwind 4
- Reverb

No usar versiones anteriores

---

## Finanzas (CRÍTICO)

Todo lo relacionado con:
- ePayco
- split
- webhooks
- ledger

requiere aprobación explícita

---

## UI

Sidebar y dashboards:
- dinámicos
- por rol
- por permisos
- por módulos

No hardcodear navegación