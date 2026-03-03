---
paths:
  - '**/*.php'
---

# Laravel Guidelines

## Core Principles

- Laravel's way first — use built-in solutions before reaching for custom code
- Thin controllers: delegate to Actions or Services; no business logic in controllers
- Prefer invokable single-action controllers (one route = one controller class)
- Dependency injection over facades in business logic
- Always eager load relationships — never cause N+1 queries

## Controllers

- Controllers orchestrate only: validate → delegate → respond
- Return typed responses: `View`, `RedirectResponse`, `JsonResponse`, `Response`
- Authorization belongs in Form Requests (`authorize()`), not controllers
- Use `Route::resource()` for standard CRUD; separate invokable controllers for custom actions

## Route Model Binding

Always use route model binding — never manually query by ID in controllers or middleware.

```php
// ✅ public function show(Url $url): View
// ❌ $url = Url::where('resource_id', $id)->firstOrFail();
```

Access resolved models in middleware via `$request->route('url')`.

## Security: Public IDs

Never expose integer `id` in URLs or API responses. See CLAUDE.md Security Rules.

```php
// ✅ 'id' => $this->resource_id   ❌ 'id' => $this->id
```

## Form Requests

Always use Form Request classes for validation and authorization — never inline `$request->validate()`.

- Rules as arrays, not pipe strings: `['required', 'string', 'max:255']`
- Access route model in `authorize()` via `$this->route('url')`

## Models

- `@property` annotations for all attributes
- Cast datetimes to `CarbonImmutable`
- Define `$fillable`; never `$guarded = []` in production
- Method order: constants → properties → casts → booted → relationships → scopes → accessors → business logic
- Query scopes for reusable query logic; not raw repeated `where()` chains

## Architecture

See CLAUDE.md for the layer rules (Services vs Jobs vs Actions).

- **Services** (`App\Services\`): external I/O, no DB writes, return DTOs
- **Jobs** (`App\Jobs\`): async workflows, persist via model methods
- **Actions** (`App\Actions\`): synchronous business operations, can persist
- **DTOs** (`App\DataTransferObjects\`): immutable, `::fromRequest()` / `::fromArray()` factories

## Routes

- Named routes always; class-array syntax `[Controller::class, 'method']`
- Group by middleware and prefix; use `Route::resource()` and `Route::apiResource()`
- Never hardcode URLs — use `route()` helper or Wayfinder on the frontend

## Configuration

- Never use `env()` directly in application code
- Kebab-case config filenames: `config/webhook-forwarding.php`

## Events

- Past-tense event names: `RequestCaptured`, `UrlDeleted`
- Listener names describe action: `SendSlackNotificationListener`

## Jobs

- Make jobs idempotent
- Implement `failed()` for error handling and logging
- Set timeout and retry values explicitly

## Testing

- Feature tests for HTTP routes, Artisan commands, and Jobs — not for internal classes
- No `RefreshDatabase` in `tests/Unit/`
- Use factories with states; one assertion concept per test

## Anti-Patterns

- ❌ Queries in views — pass data from controllers
- ❌ `env()` in application code — use `config()`
- ❌ Raw DB queries when Eloquent works
- ❌ Business logic in views — move to model methods
- ❌ Registering concrete services in providers — Laravel auto-resolves them
