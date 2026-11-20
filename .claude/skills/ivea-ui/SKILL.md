---
name: ivea-ui
description: Sistema visual obligatorio de IVEA Analítica. Úsala SIEMPRE antes de crear o modificar cualquier vista Blade, layout, componente, estilo Tailwind o gráfica Chart.js del proyecto (Login, Dashboard, Control de Módulos, Directorio de Educandos). Define tokens Tailwind v4, tipografía, paleta institucional IVEA/Veracruz, logotipo institucional único y el catálogo de componentes Blade.
---

# IVEA UI — sistema visual obligatorio

Fuentes de verdad (en este orden):

1. `docs/ui-stitch/DESIGN.md` — tokens (frontmatter) y especificación de componentes (prosa).
2. Bocetos `docs/ui-stitch/*.png` — estructura y jerarquía de cada pantalla.
3. `public/images/logo-ivea-horizontal.png` — identidad institucional.

Si algo de esta skill contradice `DESIGN.md`, gana `DESIGN.md`. Actualiza esta skill en el mismo cambio.

## Reglas no negociables

1. **Solo tokens.** Nada de hex sueltos, `bg-[#...]`, `text-[13px]`, `style="color:..."` ni la paleta por defecto
   de Tailwind (`blue-700`, `slate-500`, `emerald-600`, `gray-*`...) en vistas. Usa exclusivamente las utilidades
   generadas por el bloque `@theme` de abajo. Excepciones: los colores de Chart.js, que se leen de las
   variables CSS (ver "Gráficas"); la ficha PDF (`educandos/pdf.blade.php`), que dompdf renderiza sin el build
   de Vite y por eso lleva CSS en línea con los valores hex de `DESIGN.md` (mantenerlos sincronizados); y el
   ancho de relleno de `ui/progress` (`style="width: N%"`), que es un dato, no un token.
2. **Tipografía:** títulos y cifras KPI en `font-headline` (Plus Jakarta Sans); todo lo demás en `font-body`
   (Inter). Los bocetos PNG muestran títulos con serifa: es un artefacto del render de Stitch, **no** lo copies.
3. **Cifras con `tabular-nums`** en KPIs, tablas, porcentajes, puntajes, fechas y paginación.
4. **Componentes Blade del catálogo** (`resources/views/components/`). Si una vista necesita un patrón que ya
   existe en el catálogo, úsalo; si no existe todavía, créalo en el catálogo primero y luego úsalo. Nunca
   dupliques el markup de una tarjeta, botón, badge o input dentro de una vista.
5. **Logos solo vía `<x-brand.logo>`**: nunca un `<img src="...logo...">` directo (ver "Logotipo institucional").
6. **Español institucional** en toda la UI. Nombres de navegación fijos: *Desempeño* (Dashboard),
   *Control de Módulos*, *Directorio de Educandos*. En los bocetos aparecen "Juegos / Módulos" y "Usuarios":
   sustitúyelos por estos nombres. Las personas atendidas se llaman **educandos**, no "usuarios" ni "alumnos".
7. **Sin datos de ejemplo** del boceto (p. ej. "Prof. Roberto García", "Rosaura Domínguez"): los datos vienen del
   servidor. Para estados vacíos usa `<x-ui.empty-state>`.
8. **Alcance cerrado:** solo las 4 pantallas web del `CLAUDE.md`. El boceto `detalles_desempeno.png` es la
   referencia visual del **Directorio de Educandos** (tabla + panel lateral de detalle).

## 1. Tokens Tailwind v4 (`resources/css/app.css`)

`resources/css/app.css` debe contener exactamente este bloque `@theme` (sustituye el `--font-sans` de
Instrument Sans que trae el scaffold). No agregues tokens fuera de él sin actualizar también esta skill.

```css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';

@theme {
    /* Tipografía */
    --font-headline: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    --font-body: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-sans: var(--font-body);

    --text-headline-xl: 36px;
    --text-headline-xl--line-height: 44px;
    --text-headline-xl--font-weight: 700;
    --text-headline-xl-mobile: 28px;
    --text-headline-xl-mobile--line-height: 36px;
    --text-headline-xl-mobile--font-weight: 700;
    --text-headline-lg: 30px;
    --text-headline-lg--line-height: 38px;
    --text-headline-lg--font-weight: 700;
    --text-headline-lg-mobile: 24px;
    --text-headline-lg-mobile--line-height: 32px;
    --text-headline-lg-mobile--font-weight: 700;
    --text-headline-md: 22px;
    --text-headline-md--line-height: 28px;
    --text-headline-md--font-weight: 600;
    --text-headline-sm: 18px;
    --text-headline-sm--line-height: 24px;
    --text-headline-sm--font-weight: 600;
    --text-body-lg: 16px;
    --text-body-lg--line-height: 24px;
    --text-body-md: 14px;
    --text-body-md--line-height: 20px;
    --text-body-sm: 13px;
    --text-body-sm--line-height: 18px;
    --text-label-lg: 14px;
    --text-label-lg--line-height: 20px;
    --text-label-lg--font-weight: 600;
    --text-label-md: 12px;
    --text-label-md--line-height: 16px;
    --text-label-md--font-weight: 500;
    --text-label-sm: 11px;
    --text-label-sm--line-height: 14px;
    --text-label-sm--font-weight: 500;

    /* Superficies (DESIGN.md frontmatter) */
    --color-background: #f8f9ff;
    --color-on-background: #0b1c30;
    --color-surface: #f8f9ff;
    --color-surface-dim: #cbdbf5;
    --color-surface-bright: #f8f9ff;
    --color-surface-container-lowest: #ffffff;
    --color-surface-container-low: #eff4ff;
    --color-surface-container: #e5eeff;
    --color-surface-container-high: #dce9ff;
    --color-surface-container-highest: #d3e4fe;
    --color-surface-variant: #d3e4fe;
    --color-on-surface: #0b1c30;
    --color-on-surface-variant: #434655;
    --color-inverse-surface: #213145;
    --color-inverse-on-surface: #eaf1ff;
    --color-outline: #747686;
    --color-outline-variant: #c4c5d7;

    /* Primario: azul real */
    --color-primary: #0037b0;
    --color-on-primary: #ffffff;
    --color-primary-container: #1d4ed8;
    --color-primary-hover: #1e40af;
    --color-on-primary-container: #cad3ff;
    --color-primary-fixed: #dce1ff;
    --color-primary-fixed-dim: #b7c4ff;
    --color-on-primary-fixed: #001551;
    --color-on-primary-fixed-variant: #0039b5;
    --color-inverse-primary: #b7c4ff;
    --color-focus-ring: #dbeafe;

    /* Secundario: esmeralda (éxito, exportar, dominio) */
    --color-secondary: #006c4a;
    --color-on-secondary: #ffffff;
    --color-secondary-container: #82f5c1;
    --color-on-secondary-container: #00714e;
    --color-secondary-fixed: #85f8c4;
    --color-secondary-fixed-dim: #68dba9;
    --color-on-secondary-fixed: #002114;
    --color-on-secondary-fixed-variant: #005137;

    /* Terciario: terracota (refuerzo, prioridad) */
    --color-tertiary: #7f2500;
    --color-on-tertiary: #ffffff;
    --color-tertiary-container: #a73400;
    --color-on-tertiary-container: #ffc9b7;
    --color-tertiary-fixed: #ffdbcf;
    --color-tertiary-fixed-dim: #ffb59c;
    --color-on-tertiary-fixed: #390c00;
    --color-on-tertiary-fixed-variant: #832700;

    /* Estados */
    --color-error: #ba1a1a;
    --color-on-error: #ffffff;
    --color-error-container: #ffdad6;
    --color-on-error-container: #93000a;
    --color-warning: #b45309;
    --color-warning-fill: #d97706;
    --color-warning-container: #fef3c7;

    /* Paleta institucional IVEA (muestreada de logo-ivea-horizontal.png) */
    --color-ivea-terracota: #9a4735;
    --color-ivea-carbon: #464646;
    --color-ivea-gris: #87868a;

    /* Forma */
    --radius-sm: 0.25rem;
    --radius-control: 0.5rem;
    --radius-md: 0.75rem;
    --radius-lg: 1rem;
    --radius-xl: 1.5rem;

    /* Espaciado */
    --spacing-gutter: 1.5rem;
    --spacing-margin: 2rem;
    --spacing-space-xs: 0.25rem;
    --spacing-space-sm: 0.5rem;
    --spacing-space-md: 1rem;
    --spacing-space-lg: 1.5rem;
    --spacing-space-xl: 2rem;

    /* Elevación */
    --shadow-level-1: 0 1px 3px 0 rgb(15 23 42 / 0.05);
    --shadow-level-2: 0 10px 15px -3px rgb(15 23 42 / 0.08), 0 4px 6px -4px rgb(15 23 42 / 0.03);
    --shadow-level-3: 0 20px 25px -5px rgb(15 23 42 / 0.1), 0 8px 10px -6px rgb(15 23 42 / 0.04);
}
```

### Equivalencias que debes usar

| Necesidad | Utilidades |
|-----------|-----------|
| Lienzo de página | `bg-background text-on-background font-body` |
| Tarjeta / panel (nivel 1) | `bg-surface-container-lowest border border-outline-variant/60 rounded-lg shadow-level-1` |
| Popover / dropdown (nivel 2) | `bg-surface-container-lowest border border-outline-variant rounded-control shadow-level-2` |
| Modal (nivel 3) | `bg-surface-container-lowest rounded-xl shadow-level-3` |
| Bloque informativo tintado | `bg-surface-container-low rounded-lg` |
| Título de página | `font-headline text-headline-lg-mobile md:text-headline-lg text-on-surface` |
| Título de tarjeta | `font-headline text-headline-sm text-on-surface` |
| Texto | `text-body-md text-on-surface-variant` |
| Metadatos / eyebrow | `text-label-sm uppercase tracking-wide text-outline` |
| Cifra KPI | `font-headline text-headline-lg tabular-nums text-on-surface` |
| Separador | `border-outline-variant/60` |

Radios: tarjetas `rounded-lg`, botones/inputs/selects `rounded-control`, badges/chips/barras `rounded-full`.

Espaciado: `p-space-md`, `gap-space-sm`, `px-margin`, `gap-gutter`, etc. Los tokens de espaciado llevan el prefijo
`space-` a propósito: definir `--spacing-xl` sobrescribe `max-w-xl`/`w-xl` (quedarían en 2rem). No los renombres.

## 2. Tipografía y iconos (layout `<head>`)

```blade
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..24,400,0..1,0&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Iconos: **Material Symbols Outlined** (los mismos de los bocetos) mediante `<x-ui.icon name="bar_chart" />`.
Iconos de navegación: `bar_chart` (Desempeño), `extension` (Control de Módulos), `group` (Directorio de Educandos).

## 3. Logotipo institucional (archivo único)

El sistema usa **un solo archivo de logotipo**: `public/images/logo-ivea-horizontal.png` (1024x384, PNG
transparente). No hay logo circular ni banner de Veracruz; no los referencies ni crees placeholders para ellos.

Se pinta siempre con `<x-brand.logo />` (sin props), dimensionado con clases de altura:

| Lugar | Clases |
|-------|--------|
| Tarjeta de Login / recuperar contraseña (`layouts/guest`) | `h-12 w-auto`, centrado |
| Esquina superior izquierda del sidebar (`layouts/app`) | `h-9 w-auto`; en el riel colapsado de 72px (`md:`) se escala por ancho: `md:h-auto md:w-14 lg:h-9 lg:w-auto` |

Reglas del logo: siempre con `alt="IVEA - Instituto Veracruzano de Educación para los Adultos"` (lo pone el
componente), sobre fondo blanco o `surface-container-lowest`, nunca recoloreado, estirado (usa `h-* w-auto`), con
sombra ni sobre azul primario. Altura mínima: 36px (`h-9`, solo en el sidebar); 48px (`h-12`) en el resto.

Pie institucional: texto discreto dentro de la tarjeta de `layouts/guest`, en `text-label-md text-outline`:
`Gobierno del Estado de Veracruz • Sistema de Analítica Educativa v1.0`.

### Uso de la paleta institucional

La UI es azul (primario) por especificación; el terracota/carbón de IVEA es **acento de marca**, no de interfaz:
solo en marca (logo) y como una serie más en gráficas. Nunca en botones, enlaces, foco ni estados.

## 4. Catálogo de componentes Blade

Rutas bajo `resources/views/components/`. Props tipadas con `@props`, atributos extra con `$attributes->merge()`.

| Componente | Especificación |
|------------|---------------|
| `resources/views/layouts/app.blade.php` (`@extends`, no componente) | Shell autenticado: sidebar `w-65` (260px, `lg:`), colapsado `w-18` (72px, `md:`), drawer Alpine en móvil; topbar con breadcrumb (`@yield('title')`), nombre + `role->label()` y "Cerrar Sesión" (`danger-link`, POST). Búsqueda y campana se agregan cuando exista su funcionalidad; `main` con `px-space-md md:px-space-lg lg:px-margin` y grid 12 columnas `gap-gutter`. Tarjeta "Soporte Institucional / Mesa Técnica IVEA" al pie del sidebar. |
| `resources/views/layouts/guest.blade.php` (`@extends`) | Login, recuperar y restablecer contraseña, 403: lienzo `bg-background`, tarjeta nivel 1 centrada `w-full max-w-xl p-space-xl` con logo (`h-12 w-auto`) + eyebrow y pie institucional en texto. `<head>` compartido en `layouts/partials/head.blade.php`. |
| `brand/logo` | Ver sección 3 (archivo único, sin props). |
| `ui/icon` | `<span class="material-symbols-outlined" aria-hidden="true">{{ $name }}</span>` con tamaño por clase. |
| `ui/nav-item` | Inactivo: `text-on-surface-variant hover:bg-surface-container rounded-control`. Activo: `bg-primary text-on-primary rounded-control` + `aria-current="page"`. Prop `route`: si `Route::has()` es falso se pinta deshabilitado (`aria-disabled`, `opacity-60`), así las pantallas futuras se activan solas. En `md` la etiqueta es `sr-only`. |
| `ui/button` | `variant`: `primary` (`bg-primary-container hover:bg-primary-hover text-on-primary`), `export` (`bg-secondary text-on-secondary`), `outline` (`bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-container-low`), `danger-link` (`text-error`). Todos `h-10 px-space-md rounded-control text-label-lg inline-flex items-center gap-space-sm`, foco `focus-visible:ring-[3px] focus-visible:ring-focus-ring focus-visible:border-primary-container`. |
| `ui/input`, `ui/select` | `h-10 bg-surface-container-low border border-transparent rounded-control text-body-md`, ícono líder opcional, foco igual que botón. Label encima en `text-label-lg text-on-surface`. Error debajo en `text-body-sm text-error`. Password con toggle de visibilidad en Alpine. En formularios GET de filtros, `ui/select` va con `:use-old="false"` (tras un error, lo flasheado no es lo aplicado). |
| `ui/card` | Nivel 1 (tabla de equivalencias) + `p-space-lg`. Slots `header`, `footer` (footer en `bg-surface-container-low` como en Control de Módulos). Prop `flush` quita el padding del cuerpo (tablas a borde). |
| `ui/kpi-card` | Eyebrow `label-sm`, título `headline-sm`, cifra `headline-lg tabular-nums` + unidad `body-md`, badge de variación, barra de progreso y pie de metadatos. 4-up en `lg` (`col-span-3`), 2-up en `md`, apilado en móvil. |
| `ui/badge` | `rounded-full px-space-sm py-space-xs text-label-md`. `tone`: `success` (`bg-secondary-container text-on-secondary-container`), `info` (`bg-primary-fixed text-on-primary-fixed-variant`), `warning` (`bg-warning-container text-warning`), `danger` (`bg-error-container text-on-error-container`), `neutral` (`bg-surface-container text-on-surface-variant`). |
| `ui/progress` | Track `h-2 rounded-full bg-surface-container`; relleno por nivel: `on-track` → `bg-primary-container`, `mastered` → `bg-secondary`, `review` → `bg-warning-fill`, `risk` → `bg-tertiary-container`. `role="progressbar"` con `aria-valuenow`. |
| `ui/toggle` | Switch Alpine: activo `bg-primary-container`, inactivo `bg-surface-container-high`; etiqueta `Habilitado` (`text-primary`) / `Deshabilitado` (`text-outline`). `role="switch"` + `aria-checked`. |
| `ui/table` | Cabecera `bg-surface-container-low text-label-sm uppercase text-on-surface-variant`, filas `border-b border-surface-container hover:bg-surface-container-low`, numéricas `text-right tabular-nums`. Paginación con `{{ $paginator->links() }}` estilizada con los mismos tokens. |
| `ui/avatar` | Inicial en círculo `bg-surface-container-high text-primary font-headline`; seleccionado `bg-primary text-on-primary`. |
| `ui/filter-bar` | Tarjeta con búsqueda + selects + "Reestablecer" + acción primaria/export a la derecha. |
| `ui/segmented` | Fondo `bg-surface-container-low`, opción activa `bg-surface-container-lowest shadow-level-1`. |
| `ui/slide-over` | Panel de detalle del Directorio de Educandos: drawer derecho `max-w-md` nivel 3 sobre backdrop `bg-inverse-surface/40`; cierra con Escape, clic fuera y botón (foco al abrir, se devuelve al cerrar). Estado `open`/`close()` en el scope Alpine padre (`educandoPanel`). |
| `ui/info-banner` | `bg-surface-container-low rounded-lg p-space-lg` con ícono en `bg-surface-container-lowest rounded-control`. |
| `ui/empty-state` | Ícono `text-outline`, título `headline-sm`, texto `body-md`, acción opcional. |
| `ui/checkbox` | `size-4 rounded-sm accent-primary-container`, etiqueta `text-body-md text-on-surface-variant`, `old()` automático. |
| `ui/alert` | Mensaje de estado: `tone` `success` (`bg-secondary-container/40 text-on-secondary-container`), `info`, `danger` (`role="alert"`); ícono + texto `body-md`, `rounded-control p-space-md`. |
| `ui/chart` | `<canvas>` en contenedor de altura fija + `x-data="ivaChart(kind)"`. Datos del servidor con `:data` (atributo `data-chart`) o de una expresión Alpine del scope padre con `source` (p. ej. `detail.chart` del slide-over). |

## 5. Mapeo boceto → pantalla

| Pantalla | Boceto | Claves |
|----------|--------|--------|
| Login | `login.png` | Tarjeta centrada; sustituir el birrete por `<x-brand.logo class="h-12 w-auto" />`; subtítulo `PANEL DE DESEMPEÑO EDUCATIVO`; "Acceso al Portal"; correo institucional + contraseña; recordar sesión + enlace "¿Olvidaste tu contraseña?"; botón "Ingresar" con flecha; pie institucional. |
| Recuperar contraseña | `login.png` (misma tarjeta) | `/forgot-password`: "Recuperar Contraseña", correo + "Enviar enlace", `<x-ui.alert>` con el mensaje genérico, enlace "Volver al inicio de sesión". `/reset-password/{token}`: "Restablecer Contraseña", correo prellenado, nueva contraseña + confirmación, "Guardar nueva contraseña". |
| Desempeño (Dashboard) | `dashboard_principal.png` | Filter bar (módulo, grupo regional, rango de fechas, Actualizar, CSV) → 3 KPI → línea "Progreso Semanal" (8 col) + barras "Desempeño por Módulo" (4 col) → grupos con mayor participación + refuerzo pedagógico. |
| Control de Módulos | `control_modulos.png` | Eyebrow + título + "Guardar Cambios" → info-banner de sincronización → grid 3 col de tarjetas de módulo con toggle y footer de métricas → info-banner OTA. |
| Directorio de Educandos | `detalles_desempeno.png` | Título + chips de resumen → filter bar con "Exportar Excel/CSV" (`export`) → tabla paginada (8 col) + side-panel de detalle (4 col). |

## 6. Gráficas (Chart.js)

- Nunca escribas hex en JS: lee los tokens con
  `getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim()`.
- Orden de series: `--color-primary`, `--color-secondary`, `--color-warning-fill`, `--color-ivea-terracota`,
  `--color-primary-fixed-dim`, `--color-ivea-gris`.
- Línea: `tension: 0.35`, punto blanco con borde primario, área con degradado de `--color-primary` al 20% → 0%.
- Rejilla `--color-surface-container`, ejes y ticks `--color-outline` en Inter 11px; tooltip con fondo
  `--color-inverse-surface` y texto `--color-inverse-on-surface`.
- `responsive: true`, `maintainAspectRatio: false`; el alto lo fija el contenedor.

## 7. Responsivo y accesibilidad

- Breakpoints: móvil < `md` (4 col, `px-space-md`), tablet `md` (8 col, `px-space-lg`), escritorio `lg`/`xl` (12 col, `px-margin`).
- Contraste mínimo WCAG AA; nunca texto `text-outline` sobre `surface-container`.
- Foco visible en todo control; los íconos decorativos con `aria-hidden="true"`; botones solo-ícono con `aria-label`.
- Estados de color siempre acompañados de texto (badge "Excelente", "Refuerzo Requerido"), no solo color.

## Checklist antes de dar por terminada una vista

- [ ] Cero hex, valores arbitrarios o colores de la paleta por defecto de Tailwind en la vista
- [ ] Títulos en `font-headline`, cuerpo en `font-body`, cifras con `tabular-nums`
- [ ] Todo patrón repetible sale de `resources/views/components/`
- [ ] Logo solo vía `<x-brand.logo>` (archivo único `logo-ivea-horizontal.png`)
- [ ] Nombres de navegación y "educandos" correctos; sin datos de ejemplo del boceto
- [ ] Revisada en 375px, 768px y 1280px contra el boceto correspondiente
- [ ] Foco, `aria-*` y textos de estado presentes
