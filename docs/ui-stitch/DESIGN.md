---
name: IVEA Analítica
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#434655'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#747686'
  outline-variant: '#c4c5d7'
  surface-tint: '#2151da'
  primary: '#0037b0'
  on-primary: '#ffffff'
  primary-container: '#1d4ed8'
  on-primary-container: '#cad3ff'
  inverse-primary: '#b7c4ff'
  secondary: '#006c4a'
  on-secondary: '#ffffff'
  secondary-container: '#82f5c1'
  on-secondary-container: '#00714e'
  tertiary: '#7f2500'
  on-tertiary: '#ffffff'
  tertiary-container: '#a73400'
  on-tertiary-container: '#ffc9b7'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dce1ff'
  primary-fixed-dim: '#b7c4ff'
  on-primary-fixed: '#001551'
  on-primary-fixed-variant: '#0039b5'
  secondary-fixed: '#85f8c4'
  secondary-fixed-dim: '#68dba9'
  on-secondary-fixed: '#002114'
  on-secondary-fixed-variant: '#005137'
  tertiary-fixed: '#ffdbcf'
  tertiary-fixed-dim: '#ffb59c'
  on-tertiary-fixed: '#390c00'
  on-tertiary-fixed-variant: '#832700'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  headline-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
  headline-xl-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 22px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  margin: 2rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style
The design system delivers an institutional yet modern educational performance dashboard for 'IVEA - Panel de Desempeño' / 'IVEA Analítica'. Built for educators, academic supervisors, and regional analysts, the interface establishes an atmosphere of intellectual clarity, operational efficiency, and trustworthiness. 

The aesthetic is clean, functional minimalism with lightweight structural containers. The visual language eliminates non-essential ornamentation, relying on crisp mathematical structure, precise information hierarchy, and calm surface contrast to make dense academic analytics immediately readable and actionable.

## Colors
The palette balances institutional authority with high-contrast accessibility:

- **Canvas Background:** `#f8fafc` provides a cool, low-fatigue slate-white foundation that separates clearly from content layers.
- **Card & Container Surfaces:** `#ffffff` for sharp, distinct data islands.
- **Primary Accent (`#1d4ed8`):** Royal blue directs attention to key KPIs, selected views, primary actions, and cohort progression milestones.
- **Secondary / Success Accent (`#059669`):** Fresh emerald green handles positive variance, mastery benchmarks, and data export triggers.
- **Neutral Scale (`#64748b` base):** Deep slate (`#0f172a` for titles, `#334155` for body, `#64748b` for metadata) replaces pure black to soften readability while maintaining strict WCAG AAA legibility.
- **Structural Outlines:** Subtle cool gray (`#e2e8f0` to `#e5e7eb`) defines structural boundaries without visual noise.

## Typography
The system employs a dual-typeface structure tailored to data visualization and analytical tools:

- **Plus Jakarta Sans** provides open, legible apertures for dashboard metrics, title headers, and summary metric cards.
- **Inter** handles high-density tables, multi-tiered filter menus, navigation labels, and raw statistical values with strict tabular numerical alignment.
- Tabular figures (`tnum`) must be enforced across all metrics, data tables, and timeline trackers to avoid alignment shifts across dynamic updates.

## Layout & Spacing
The layout relies on a fluid 12-column grid anchored by a structured left navigation rail (collapsed at 72px or expanded at 260px):

- **Desktop (1280px+):** 12-column layout with 24px (`1.5rem`) gutters and 32px (`2rem`) outer margin. KPI summaries span 3 columns each (4-up layout), while detailed analytics and charts span 6 to 8 columns.
- **Tablet (768px - 1279px):** 8-column layout with 20px gutters and 24px margins. Cards reflow into 2-column KPI grids, and charts adapt to full container width.
- **Mobile (<768px):** 4-column layout with 16px gutters and 16px margins. Metrics stack vertically with swipeable horizontal tabs for data filtering.

## Elevation & Depth
The system utilizes low-contrast outlines coupled with ambient micro-shadows to ensure high interface performance and a neat paper-like aesthetic:

- **Level 0 (Canvas):** Flat `#f8fafc` background.
- **Level 1 (Cards, Modules, KPI Containers):** Pure white background (`#ffffff`), bounded by a 1px solid border in `#e2e8f0`, accompanied by an ambient drop shadow: `0 1px 3px 0 rgba(15, 23, 42, 0.05)`.
- **Level 2 (Popovers, Filter Menus, Dropdowns):** `#ffffff` with a 1px border in `#cbd5e1` and an elevated shadow: `0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.03)`.
- **Level 3 (Modal Overlays, Export Dialogs):** `#ffffff` with high-elevation diffusion: `0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.04)`.

## Shapes
A clean, soft-geometry approach establishes an organized, modern interface:

- **Cards & Data Panels:** `rounded-lg` (16px / `1rem`) creates distinct visual containers with balanced corners.
- **Buttons, Inputs & Interactive Selectors:** `rounded` (8px / `0.5rem`) keeps controls compact and uniform within form structures.
- **Badges, Status Chips & Metric Deltas:** Full pill radius (`rounded-full` / `9999px`) provides visual contrast against rectangular data cards.

## Components

### Buttons
- **Primary:** Solid royal blue (`#1d4ed8`) fill, white text, 8px radius, subtle hover transition to `#1e40af`.
- **Secondary / Export:** Emerald green (`#059669`) fill or light-emerald tint (`#ecfdf5`) with `#047857` text for data export and completion workflows.
- **Tertiary / Outline:** White surface with 1px border in `#e2e8f0`, text in `#334155`, hover background `#f1f5f9`.

### Metric Cards (KPI Units)
- Structured white surface with a 1px `#e2e8f0` border and 16px corner radius.
- Displays small muted category label (`label-sm`), prominent metric value (`headline-lg` with tabular figures), and a compact pill badge indicating percentage change (emerald for positive gain, rose for regression).

### Data Tables
- Header row styled with uppercase muted slate text (`label-sm`), light background (`#f8fafc`), and a bottom border in `#e2e8f0`.
- Alternating or clean hover row states using `#f8fafc`, row separation via 1px border `#f1f5f9`.
- Numeric columns right-aligned with fixed tabular glyph spacing.

### Filter Bars & Chips
- Segmented filter controls with light background (`#f1f5f9`), shifting to pure white with light shadow when active.
- Compact filter chips with 1px `#cbd5e1` outline and removable dismiss icons for active educational cohorts and date ranges.

### Input Fields & Selects
- Height fixed at 40px, `#ffffff` surface, 1px border `#cbd5e1`, 8px border radius.
- Active focus state: royal blue border (`#1d4ed8`) with a 3px ring of `#dbeafe` (alpha 0.5).

### Progress & Mastery Bars
- Track background in `#f1f5f9`, 8px height, rounded full.
- Active fill dynamically colored by performance tier (Royal Blue for on-track, Emerald for mastered, Amber for review required).