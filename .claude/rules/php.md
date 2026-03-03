---
paths:
  - '**/*.php'
---

# PHP Guidelines

## Core

- `declare(strict_types=1)` at the top of every file
- `final` classes and `readonly` properties by default — mutability must be justified
- Type-hint every parameter and return; avoid `mixed`
- Early returns over nested conditionals
- Constructor property promotion for simple assignments
- `match` over `switch` when returning a value
- `fn` arrow functions for single-expression callbacks; full closures for multi-statement logic
- `mb_*` string functions; `===`/`!==` comparisons; `DateTimeImmutable` over `DateTime`
- Remove unused imports; no fully-qualified class names in docblocks

## Naming

| Thing | Convention | Example |
|---|---|---|
| Class | PascalCase noun | `CaptureRequestAction` |
| Interface | PascalCase + `Interface` | `HttpClientInterface` |
| DTO | PascalCase + `DTO` | `CapturedRequestDTO` |
| Method | camelCase verb | `captureRequest()` |
| Boolean method | question form | `isActive()`, `canForward()` |
| Variable | camelCase | `$capturedRequests` |
| Constant | UPPER_SNAKE_CASE | `MAX_BODY_SIZE` |

## Comments

Comments explain **WHY**, not WHAT. Never restate what the code obviously does.

```php
// ✅ Business rule: free tier URLs expire after 1 hour
// ❌ Check if user is free
```

### Docblocks

Only add a docblock when it adds information that cannot be expressed in the PHP type signature:

- `@throws` for checked exceptions callers must handle
- `@param array<string, SomeType>` when the array shape matters and PHP can't express it
- `@var` on untyped parent class properties (e.g. Laravel's `$signature`, `$description`)

**Never** add a docblock that only restates the method name or what the code obviously does:

```php
// ❌ Pointless — says nothing the method name doesn't already say
/**
 * Display the user's profile form.
 */
public function edit(): Response

// ❌ Pointless — says nothing the type hints don't already say
/**
 * @param string $name
 * @return void
 */

// ✅ Adds value — callers must handle this exception
/** @throws ValidationException */
public function store(Request $request): RedirectResponse

// ✅ Adds value — array shape can't be expressed in PHP types
/** @param array<string, mixed> $data */
public function execute(User $user, array $data): User
```

## Exceptions

- Custom exceptions extend appropriate base class, named constructor: `UrlNotFoundException::withResourceId($id)`
- Catch specific types; never empty catch blocks
- Throw at boundaries (controllers, commands), not deep in business logic

## Style

- 4 spaces, max 120 chars per line, trailing commas in multi-line arrays
- One class per file; blank line after opening `<?php`

## Code Quality Automation

**Rector** is configured and runs as part of the `composer run test` suite (dry-run). It enforces:

- PHP 8.4 syntax upgrades
- Dead code removal (`SetList::DEAD_CODE`)
- Code quality improvements (`SetList::CODE_QUALITY`)
- Early return rewrites (`SetList::EARLY_RETURN`)
- Type declaration completeness (`SetList::TYPE_DECLARATION`)
- Laravel-specific patterns up to Laravel 12 (`LaravelLevelSetList::UP_TO_LARAVEL_120`)

When writing new code, avoid patterns Rector would rewrite — write the idiomatic form directly. If `composer run rector` reports violations, fix them with `composer run rector:fix` then commit the result.

Rector skips `database/migrations/` — generated migration files are excluded intentionally.

## Coverage

Never use `@codeCoverageIgnore` annotations (inline or block). All code paths must be exercised by
tests. If a path cannot be reached through a normal entry point (HTTP route, Artisan command, Job),
the code should be restructured or removed — not annotated.
