---
paths:
  - '**/*.tsx'
  - '**/*.jsx'
---

# React Guidelines

## Core

- Functional components with hooks only — no class components
- One component per file; co-locate tests as `component-name.test.tsx` (no `__tests__` dirs)
- TypeScript for every component; explicit prop interfaces
- `interface` for props, not inline object types

## Components

- Keep components small and single-responsibility
- Props interface named `<ComponentName>Props`
- Default props via destructuring defaults, not `defaultProps`
- Conditional rendering: `&&` for show/hide, ternary for either/or — avoid nested ternaries

## Hooks

- All hook deps must be correct — never suppress the exhaustive-deps warning without a comment
- Custom hooks prefixed with `use`; return objects for named values, tuples for pairs
- Local state first (`useState`) — lift only when necessary
- `useCallback` for callbacks passed to child components; `useMemo` for expensive computations

## Inertia.js

- Use Wayfinder helpers from `@/wayfinder` — never hardcode URLs
- `<Link>` for navigation, `router.*` for programmatic navigation — never `<a href>`
- Type every page component's props interface

```tsx
// ✅ import { urls } from '@/wayfinder'; <Link href={urls.show.url(url)}>
// ❌ <a href={`/urls/${url.id}`}>
```

## State Management

- Local state by default; Inertia `useForm` for form state
- No global state libraries unless clearly justified
- List keys: always stable unique IDs, never array index for dynamic lists

## Performance

- Avoid premature memoisation — only `useMemo`/`useCallback` when there's a measured need
- Stable keys in lists prevent unnecessary re-renders

## shadcn/ui

- Use components from `@/components/ui` before building custom ones
- Use `variant` and `size` props rather than overriding with className
- Compose with `asChild` when wrapping Inertia `<Link>` in a `<Button>`

## Testing

- React Testing Library + Vitest
- Test user behaviour, not implementation details
- Mock Inertia router in component tests
- Test complex logic and user interactions; skip trivial presentational components

## Don't

- Class components
- `any` type
- Mutate state directly
- Index as list key (for dynamic lists)
- Hardcode URLs — use Wayfinder
- Nested ternaries in JSX
- `__tests__` directories — keep tests next to source files
