# Contributing to Viewhook

Thank you for your interest in contributing to Viewhook! This document describes how to set up your development environment and the conventions we follow.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/viewhook.git`
3. Copy the environment file: `cp .env.example .env`
4. Start Laravel Sail: `./vendor/bin/sail up -d`
5. Install PHP dependencies: `./vendor/bin/sail composer install`
6. Install Node dependencies: `npm install`
7. Generate app key: `./vendor/bin/sail artisan key:generate`
8. Run migrations: `./vendor/bin/sail artisan migrate`
9. Build frontend assets: `npm run dev`

## Code Style

### PHP

We use [Laravel Pint](https://laravel.com/docs/pint) for code style enforcement (PSR-12 + Laravel conventions).

```bash
# Auto-fix code style
./vendor/bin/sail composer run lint:fix

# Check only (no fix)
./vendor/bin/sail composer run lint
```

### TypeScript / JavaScript

We use [ESLint](https://eslint.org/) and [Prettier](https://prettier.io/) for frontend code quality.

```bash
# Auto-fix ESLint issues
npm run lint

# Auto-fix Prettier formatting
npm run format

# Check only (no fix)
npm run lint:check
npm run format:check
```

## Static Analysis

We run [PHPStan](https://phpstan.org/) at level 8 on all application code.

```bash
./vendor/bin/sail composer run types
```

## Testing

All code must be covered by tests. We require 100% minimum code coverage.

```bash
# Run all backend checks (lint + types + tests)
./vendor/bin/sail composer run test

# Run individual suites
./vendor/bin/sail composer run test:unit
./vendor/bin/sail composer run test:feature

# Run all frontend checks (eslint + prettier + tsc + vitest)
npm test

# Run frontend unit tests only
npm run test:unit
```

### Writing Tests

- **Feature tests**: Test from HTTP endpoints, Artisan commands, or Jobs only. Never test Actions, Services, or Models directly.
- **Unit tests**: No database, no Laravel context required.
- Tests live in `tests/Feature/` and `tests/Unit/`.
- Follow the existing Pest PHP conventions.

## Architecture

Please follow the existing layered architecture:

| Layer        | Responsibility                                              |
| ------------ | ----------------------------------------------------------- |
| **Actions**  | Single-purpose business logic, delegates to models/services |
| **Services** | External I/O (HTTP calls), return DTOs, never write to DB   |
| **Jobs**     | Async work, call services then persist via models           |
| **DTOs**     | Immutable data containers between layers                    |

## Pull Request Process

1. Ensure `./vendor/bin/sail composer run test` passes (all 4 checks green)
2. Ensure `npm test` passes (all frontend checks green)
3. Write or update tests for any changed behaviour
4. Keep PRs focused — one feature or fix per PR
5. Write a clear description of what the PR changes and why
6. Reference any related issues

## Commit Messages

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>: <description>
```

Types: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `perf`, `ci`

Examples:

- `feat: add custom domain support`
- `fix: prevent duplicate requests on retry`
- `docs: update self-hosting guide`

## Reporting Issues

Please use the GitHub issue templates:

- [Bug Report](.github/ISSUE_TEMPLATE/bug_report.md)
- [Feature Request](.github/ISSUE_TEMPLATE/feature_request.md)

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Built by [Paul Underwood](https://paulund.co.uk).
