---
paths:
  - '**/*.css'
  - '**/*.tsx'
  - '**/*.jsx'
---

# CSS / Tailwind Guidelines

## Core

- Tailwind utility classes first — no custom CSS for things Tailwind provides
- Mobile-first responsive design (`sm:`, `md:`, `lg:` prefixes)
- `cn()` from `@/lib/utils` for conditional classes — no string concatenation
- Extract repeated class patterns into components, not CSS
- No inline styles; no `!important`; no arbitrary values when Tailwind scale exists

## Semantic Colours

| Intent | Text | Background | Border |
|---|---|---|---|
| Success / active | `green-600` | `green-50` | `green-200` |
| Error / failed | `red-600` | `red-50` | `red-200` |
| Warning | `amber-600` | `amber-50` | `amber-200` |
| Info / primary | `blue-600` | `blue-50` | `blue-200` |
| Neutral | `muted-foreground` | `muted` | `border` |

Use `dark:` variants for dark mode support.

## Typography

| Role | Classes |
|---|---|
| Page title | `text-3xl font-bold` |
| Section heading | `text-2xl font-semibold` |
| Card title | `text-xl font-semibold` |
| Body | `text-base` |
| Caption / label | `text-sm text-muted-foreground` |
| Monospace (paths, headers) | `font-mono text-sm` |

## Layout

- Page container: `container mx-auto max-w-7xl px-4 py-8`
- Card grid: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6`
- Section spacing: `space-y-8`; content spacing: `space-y-4`; tight: `space-y-2`
- Card padding: `p-6`; grid gaps: `gap-4` / `gap-6`

## Transitions

- Card hover: `transition-shadow hover:shadow-md`
- Button: `transition-colors`
- Entrance: `transition-opacity duration-300`

## Accessibility

- Always visible focus ring: `focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2`
- WCAG AA contrast (4.5:1 for text); never rely on colour alone

## Icons (Lucide React)

- `h-4 w-4` small (16px), `h-5 w-5` medium (20px), `h-6 w-6` large (24px)
- Icons inside text containers inherit colour automatically

## Custom CSS (last resort)

Use `@layer components` or `@layer utilities` with `@apply`. Use CSS custom properties for theme values.

## Don't

- Inline styles
- Custom CSS for things Tailwind provides
- `!important`
- Arbitrary values (`w-[256px]`) when Tailwind scale exists
- Skip focus states
- String concatenation for conditional classes
