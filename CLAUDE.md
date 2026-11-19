# IVEA Analítica — Project CLAUDE.md

> Basado en la plantilla `ecc-repo/examples/laravel-api-CLAUDE.md`, adaptado a un monolito full-stack.

## Project Overview

**Sistema:** IVEA Analítica

**Tipo:** Monolito full-stack (vistas web renderizadas en servidor + una API mínima para la app móvil).

**Stack:** PHP 8.3+, Laravel 11, Blade, Tailwind CSS, Alpine.js, Chart.js, PostgreSQL, Vite, PHPUnit/Pest

> Nota: `composer.json` declara actualmente `laravel/framework ^13.17` y `.env.example` usa `DB_CONNECTION=sqlite`.
> La especificación del proyecto es Laravel 11 + PostgreSQL; alinear dependencias y `.env` antes de construir sobre ellas.

**Arquitectura:** Controladores delgados -> services -> actions, Eloquent ORM, Form Requests para validación,
vistas Blade con componentes, interactividad ligera con Alpine.js y gráficas con Chart.js. API Resources solo
para los endpoints móviles.

## Alcance del sistema (cerrado)

El sistema tiene **exactamente 4 módulos web** y **2 endpoints API v1**. No agregar módulos, pantallas ni
endpoints nuevos sin aprobación explícita.

### Módulos web (Blade)

| # | Módulo | Boceto de referencia |
|---|--------|----------------------|
| 1 | Login | `docs/ui-stitch/login.png` |
| 2 | Dashboard | `docs/ui-stitch/dashboard_principal.png`, `docs/ui-stitch/detalles_desempeno.png` |
| 3 | Control de Módulos | `docs/ui-stitch/control_modulos.png` |
| 4 | Directorio de Educandos | (sin boceto aún; seguir los tokens de `DESIGN.md`) |

### API v1 (app móvil)

- 2 endpoints bajo el prefijo `/api/v1`, consumidos únicamente por la app móvil.
- Autenticación vía Sanctum (tokens).
- Rutas en `routes/api.php` agrupadas con `Route::prefix('v1')`.

## Diseño e identidad visual

- **Tokens de diseño y bocetos:** `docs/ui-stitch/`
  - `DESIGN.md` es la fuente de verdad de colores, tipografía, espaciado y radios. Mapear sus tokens al tema de
    Tailwind (`@theme` en `resources/css/app.css`); no usar colores hex sueltos en las vistas.
  - Los `.png` son los bocetos de cada pantalla; replicar su estructura y jerarquía visual.
- **Logos institucionales:** `public/images/` (p. ej. `logo-ivea-horizontal.png`). Referenciarlos con
  `asset('images/...')`; no modificar ni recolorear los logos.
- Gráficas con Chart.js usando la paleta de `DESIGN.md`.

## Critical Rules

### PHP Conventions

- `declare(strict_types=1)` en todos los archivos PHP
- Propiedades tipadas y tipos de retorno en todo el código
- Clases `final` para services y actions
- Nada de `dd()` ni `dump()` en código commiteado
- Formato con Laravel Pint (PSR-12)

### Frontend (Blade + Tailwind + Alpine + Chart.js)

- Layout base compartido en `resources/views/layouts/`; componentes reutilizables en `resources/views/components/`
- Alpine.js solo para interactividad de UI (menús, modales, filtros); la lógica de negocio vive en el servidor
- Los datos de las gráficas se preparan en services y se pasan a la vista ya agregados (`@json(...)`)
- Escapar siempre la salida con `{{ }}`; `{!! !!}` solo para contenido de confianza
- Assets vía Vite (`@vite(['resources/css/app.css', 'resources/js/app.js'])`)

### API Response Envelope (solo API v1)

```json
{
  "success": true,
  "data": {"...": "..."},
  "error": null,
  "meta": {"page": 1, "per_page": 25, "total": 120}
}
```

### Database (PostgreSQL)

- Migraciones versionadas en git
- Eloquent o query builder (sin SQL crudo salvo parametrizado)
- Indexar toda columna usada en `where` u `orderBy`
- Paginar los listados (p. ej. Directorio de Educandos)

### Authentication & Authorization

- Web: sesión de Laravel (módulo Login), rutas protegidas con middleware `auth`
- API v1: Sanctum
- Policies para autorización a nivel de modelo

### Validation

- Form Requests para toda entrada (web y API)
- Transformar la entrada a DTOs para la lógica de negocio

### Error Handling

- Excepciones de dominio en services
- Mapear excepciones a respuestas en `bootstrap/app.php` vía `withExceptions`
- Nunca exponer errores internos al cliente

### Code Style

- Sin emojis en código ni comentarios
- Longitud máxima de línea: 120 caracteres
- Controladores delgados; services y actions contienen la lógica

## File Structure

```
app/
  Actions/
  Http/
    Controllers/
      Api/V1/
    Requests/
    Resources/
  Models/
  Policies/
  Services/
docs/
  ui-stitch/          # DESIGN.md (tokens) + bocetos .png
public/
  images/             # logos institucionales
resources/
  css/app.css         # tokens de DESIGN.md mapeados a Tailwind
  js/app.js           # Alpine.js + Chart.js
  views/
    layouts/
    components/
    auth/             # Login
    dashboard/
    modulos/          # Control de Módulos
    educandos/        # Directorio de Educandos
routes/
  web.php
  api.php             # /api/v1
```

<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
