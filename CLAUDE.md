# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Viewhook.dev** (internal name: Viewhook) — a webhook testing application (like RequestBin/Webhook.site). Built with Laravel 12 + Inertia.js + React 18 + TypeScript. Tailwind CSS v4 for styling. SQLite for dev, TiDB Serverless (MySQL-compatible) in production.

## Commands

### Backend

All backend commands must run inside Sail:

```bash
./vendor/bin/sail composer run test              # All checks: lint (Pint) + rector (dry-run) + types (PHPStan level 8) + tests (Pest with 100% coverage)
./vendor/bin/sail composer run lint               # Pint style check (no fix)
./vendor/bin/sail composer run lint:fix            # Pint auto-fix
./vendor/bin/sail composer run rector             # Rector dry-run (check only)
./vendor/bin/sail composer run rector:fix         # Rector auto-fix
./vendor/bin/sail composer run types               # PHPStan static analysis
./vendor/bin/sail composer run test:unit           # Pest unit tests only
./vendor/bin/sail composer run test:feature        # Pest feature tests only
./vendor/bin/sail composer run test:both           # Unit + Feature with 100% min coverage
```

Run a single test by name:

```bash
./vendor/bin/sail ./vendor/bin/pest --filter=TestName
```

### Frontend

```bash
npm test                # All checks: eslint + prettier + tsc + unit tests
npm run lint            # ESLint auto-fix
npm run lint:check      # ESLint check only
npm run format          # Prettier auto-fix
npm run format:check    # Prettier check only
npm run types           # TypeScript type check (tsc --noEmit)
npm run dev             # Vite dev server
npm run build           # Production build
```

### Development Environment

Uses Laravel Sail (Docker): `./vendor/bin/sail up -d`

**Important**: All backend commands (composer, artisan, pest) must run inside Sail via `./vendor/bin/sail <command>`. Ensure Sail is running before executing backend commands.

After modifying routes, regenerate TypeScript helpers: `./vendor/bin/sail php artisan wayfinder:generate` (outputs to `resources/js/wayfinder/`). Use `route.url()` as a function call, not property access.

## Architecture

### Backend

- **Actions** (`app/Actions/`): Single-purpose action classes (main business logic entry points). Controllers delegate to these.
- **DTOs** (`app/DataTransferObjects/`): Immutable data containers with `::fromRequest()` / `::fromArray()` factories.
- **Services** (`app/Services/`): External I/O orchestration (export, rate limiting, webhook forwarding).
- **Jobs** (`app/Jobs/`): Async work — `CleanupExpiredRequestsJob`, `ForwardWebhookJob`, `SendSlackNotificationJob`.
- **Events** (`app/Events/RequestCaptured`): Broadcasts on private WebSocket channel `urls.{resourceId}` via Laravel Reverb.

### Frontend

- Inertia.js pages in `resources/js/Pages/`, layouts in `resources/js/Layouts/`
- Vite alias: `@` maps to `resources/js/`
- Real-time via `laravel-echo` + `pusher-js` connecting to Laravel Reverb
- Wayfinder-generated route helpers in `resources/js/wayfinder/`

### Key Domain Concepts

- **Url model**: Webhook endpoints identified by UUID `resource_id` (used as route key). Captures requests at `POST|GET|PUT|... /catch/{uuid-or-slug}/{optional-path}`.
- **Request model**: Captured webhook data. Route key is `resource_id`.
- **Capture flow**: `CaptureController` → `CaptureRequestAction` → `CapturedRequestDTO` → save → broadcast `RequestCaptured` event → optionally dispatch forwarding/notification jobs.
- **All models** use `declare(strict_types=1)`, are `final`, and share a `HasResourceId` concern for UUID-based routing.

### Auth

Laravel Sanctum + Breeze auth controllers.

## Testing

- **Framework**: Pest PHP with Laravel plugin
- **Test env** (`phpunit.xml`): SQLite in-memory, array cache, sync queue, null broadcast
- **Coverage**: 100% minimum enforced. Auth controllers are excluded from coverage source.
- **PHPStan**: Level 8, covers `app/`, `database/`, `routes/`
- **Feature tests must be written from entry points only**: HTTP endpoints (`$this->get/post/...`), Artisan commands (`$this->artisan()`), or Jobs (`$job->handle()`). Never test Actions, Services, Form Requests, or Model methods directly in feature tests.

## Infrastructure

- **Queue/Cache/Sessions**: All database-backed (no Redis)
- **WebSockets**: Laravel Reverb
- **Scheduler**: Standard cron running `php artisan schedule:run` every minute

## Security Rules

### Never Expose Internal Database IDs

**CRITICAL**: Never expose the integer `id` column in public URLs, API responses, or frontend code.

- **Internal `id`**: Integer primary key — used only for database relationships and internal queries
- **Public `resource_id`**: UUID — used in all URLs, API responses, and TypeScript types

```php
// ✅ CORRECT: Expose resource_id as 'id'
public function toArray(Request $request): array
{
    return [
        'id' => $this->resource_id,  // UUID string
        'name' => $this->name,
    ];
}

// ❌ WRONG: Never expose internal ID
public function toArray(Request $request): array
{
    return ['id' => $this->id];  // Exposes enumerable integer
}
```

```typescript
// ✅ CORRECT: id is always a UUID string in TypeScript
interface WebhookUrl {
    id: string; // resource_id from backend
    name: string;
}
```

Models using `HasResourceId`: `Url`, `Request` (all public-facing models).

When adding a new public model: add migration for `resource_id`, add `HasResourceId` trait, update API Resource, update TypeScript type, regenerate Wayfinder routes.

## Architectural Patterns

### Layer Rules

| Layer         | Responsibility                               | Can persist to DB?      |
| ------------- | -------------------------------------------- | ----------------------- |
| Actions       | Business logic, orchestrate within a request | Yes (via models)        |
| Services      | External I/O (HTTP calls, APIs)              | **No**                  |
| Jobs          | Async workflows                              | Yes (via model methods) |
| DTOs          | Data transport between layers                | N/A                     |
| Value Objects | Domain concepts with validation              | N/A                     |

- **Services** orchestrate external I/O and return DTOs. They **never** write to the database directly.
- **Jobs** call services and then persist results via model factory/upsert methods.
- **DTOs** (`App\DataTransferObjects\`) — many properties, transfer data across layer boundaries.
- **Value Objects** (`App\ValueObjects\`) — few properties, encapsulate domain validation (e.g., a slug format).

### DTO Pattern

```php
final readonly class CapturedRequestDTO
{
    public function __construct(
        public string $method,
        public string $path,
        public array $headers,
        public string $body,
    ) {}

    public static function fromRequest(IlluminateRequest $request): self
    {
        return new self(
            method: $request->method(),
            path: $request->path(),
            headers: $request->headers->all(),
            body: $request->getContent(),
        );
    }
}
```

### Service Pattern

```php
// Services orchestrate external I/O — no DB writes
final readonly class WebhookForwardingService
{
    public function forward(WebhookRequest $request, string $targetUrl): ForwardingResultDTO
    {
        $response = $this->http->post($targetUrl, $request->body);
        return ForwardingResultDTO::fromResponse($response);
    }
}
```

### Job Pattern

```php
// Jobs call services then persist results
final class ForwardWebhookJob implements ShouldQueue
{
    public function handle(WebhookForwardingService $service): void
    {
        $result = $service->forward($this->request, $this->request->url->forward_url);
        $this->request->update(['forwarding_status' => $result->status]);
    }
}
```

## Learning from Mistakes

When you make a mistake and the user corrects you, immediately propose capturing the fix as a permanent rule. Say:

> "This looks like a pattern worth adding to the rules. Should I update `[file]`?"

Then show the exact text to add. On approval, write it directly to the appropriate file:

- `.claude/rules/*.md` — conventions that apply broadly across the codebase
- `.claude/agents/*.md` — corrections to how a specific agent behaves
- `.claude/skills/*/SKILL.md` — workflow improvements for a skill

Do this in the same response as the correction — not as a follow-up.

## Git Conventions

Conventional commit format:

```
<type>: <description>

<optional body>
```

Types: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `perf`, `ci`

Attribution is disabled globally — do not add `Co-Authored-By` lines.
