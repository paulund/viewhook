---
paths:
  - '**/*.ts'
  - '**/*.tsx'
---

# TypeScript Guidelines

## Core

- Strict mode always on (`tsconfig.json`)
- No `any` — use `unknown` when type is truly unknown
- No `@ts-ignore` without an explanatory comment
- No `Function`, `Object`, or `{}` types — use specific signatures/interfaces
- Prefer type inference where it's obvious; explicit types at function boundaries

## Type Definitions

- One file per domain type in `resources/js/types/`, kebab-case filename (`url.d.ts`)
- Re-export all from `index.d.ts`; import from `@/types` in components
- `interface` for object shapes; `type` for unions, intersections, and aliases
- Optional (`?`) for may-be-undefined; `string | null` for explicitly nullable

## Key Rules

- `id` fields on public models are `string` (UUID), never `number`
- Prefer string literal unions over string enums: `type Method = 'GET' | 'POST'`
- Use built-in utility types: `Partial<T>`, `Pick<T, K>`, `Omit<T, K>`, `Record<K, V>`
- Type async functions explicitly: `async function fetchUrl(id: string): Promise<WebhookUrl>`
- Use discriminated unions for result types: `{ status: 'ok'; data: T } | { status: 'error'; message: string }`

## Inertia Page Props

```ts
interface UrlShowProps {
    url: WebhookUrl;
    requests: WebhookRequest[];
}
export default function UrlShow({ url, requests }: UrlShowProps) { ... }
```

## Don't

- `any` type
- `@ts-ignore` without comment
- `Function` / `Object` / `{}` types
- Duplicate type definitions — import and re-export
- Over-engineer types for simple shapes
