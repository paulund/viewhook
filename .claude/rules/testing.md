---
paths:
  - '**/tests/**/*.php'
---

# Testing Guidelines

## Core Philosophy

Feature tests verify behaviour through entry points. Unit tests verify isolated pure logic.

**Entry points** (the only things feature tests should call directly):
1. **HTTP routes** — via `$this->get/post/put/delete()`
2. **Artisan commands** — via `$this->artisan()`
3. **Jobs** — via `$job->handle()`

## Feature Tests: What to Test

Assert from the outside:
- HTTP status codes and redirects
- Inertia component and props: `$response->assertInertia(fn($p) => $p->component('urls/show'))`
- Database state: `assertDatabaseHas`, `assertDatabaseMissing`
- Dispatched jobs: `Queue::fake()` + `Queue::assertPushed()`
- Dispatched events: `Event::fake()` + `Event::assertDispatched()`
- Authorization outcomes (403, redirect to login)
- Validation errors: `assertSessionHasErrors('field')`

## Feature Tests: What NOT to Test Directly

Never instantiate these in a feature test with `new SomeClass()`:

- ❌ Actions — test through the controller or job that calls them
- ❌ Form Requests — test validation through route form submissions
- ❌ Services — test through the route or job that uses them
- ❌ Model methods/relationships — test through routes that exercise them
- ❌ Policies — test through route authorization checks

## Unit Tests

Write unit tests only when:
- Logic is **complex** and benefits from isolation
- There is **no practical entry point** to test through
- The code is **pure** — no database, no external I/O

Belongs in unit tests: value object validation, enum mappings, pure transformation functions, algorithm implementations.

Does **not** belong in unit tests: anything touching the database (use Feature tests), anything with Laravel boot context.

## Decision Tree

```
Entry point (route / command / job)?
  YES → Feature test
  NO  → Needs database?
          YES → Feature test (go through an entry point)
          NO  → Complex logic?
                  YES → Unit test
                  NO  → Skip (implicitly covered)
```

## Test Organisation

```
tests/Feature/
├── Auth/
├── Urls/
├── Capture/
├── Console/Commands/
├── Jobs/
└── Api/

tests/Unit/
├── Enums/
├── Services/
└── ValueObjects/
```

Organise by domain, not by class type. No `UrlControllerTest` — use `Urls/UrlShowTest`.

## Monolithic Test File Anti-Pattern

**Never create large single test files that combine multiple domains or features.**

❌ **Bad**: `MissingCoverageTest.php` (790 lines with Dashboard, Profile, Auth, Teams, Subscriptions, Forwarding tests)

✅ **Good**: Split into domain-specific files:
- `Dashboard/DashboardControllerTest.php`
- `Profile/ProfileEditTest.php`
- `Profile/ProfileUpdateTest.php`
- `Auth/LoginRequestTest.php`
- `Teams/TeamModelTest.php`
- `Subscriptions/StripeWebhookControllerTest.php`

**Guidelines:**
- One feature/domain per file (e.g., a single controller's tests)
- If a file grows beyond ~200 lines, split it into separate focused files
- Use subdirectories to organize related domains (e.g., `Profile/`, `Forwarding/`, `Subscriptions/`)
- File names describe the feature being tested, not the class: `ProfileUpdateTest`, not `UpdateRequestTest`

**Rationale:**
- Large files are hard to navigate and understand
- Domain organization matches the codebase architecture (controllers, services, domains)
- Easier to locate tests for a specific feature
- Reduces merge conflicts when multiple developers work on tests

## What NOT to Test

- Framework behaviour (Eloquent relationships, casts, timestamps) — trust Laravel
- Trivial getters/constructors with no logic
- Third-party package internals — mock them

## Style

- Pest PHP; descriptive `it('does something specific')` descriptions
- Arrange–Act–Assert; one assertion concept per test
- Use factories with states; no raw model construction
- `Queue::fake()` / `Event::fake()` / `Http::fake()` at the start of tests that need them
