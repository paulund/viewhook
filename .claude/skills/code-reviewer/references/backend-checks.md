# Backend Checks

To ensure code quality and maintainability, the backend code must pass several checks before being committed. These checks include linting, type analysis, and running tests. Below are the commands to run these checks using Laravel Sail (Docker).

## Running Backend Checks

```bash
# Run all checks (lint, types, tests) - must pass before committing
composer run test
# Individual commands
composer run lint              # Fix code style with Pint
composer run refactor          # Apply Rector transformations
composer run test:lint         # Check Pint style (no fix)
composer run test:types        # Run PHPStan analysis
composer run test:tests        # Run Pest tests with coverage
composer run test:filter=Name  # Run single test by name
```

## Database Migrations

Always run database migrations via Sail to ensure they are executed in the correct environment:

```bash
./vendor/bin/sail artisan migrate
```
