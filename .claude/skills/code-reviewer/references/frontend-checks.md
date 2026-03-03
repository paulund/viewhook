# Frontend Checks

To maintain code quality and consistency in the frontend, all code must pass several checks before being committed. These checks include linting, formatting, type checking, and running tests. Below are the commands to run these checks for the React/TypeScript frontend.

## Running Frontend Checks

```bash
npm test                    # Run all checks (lint, format, types, vitest)
npm run lint                # Fix ESLint issues
npm run format              # Fix Prettier issues
npm run types               # TypeScript type checking
npm run test:unit           # Run Vitest tests
npm run test:unit:watch     # Vitest in watch mode
npm run dev                 # Start Vite dev server
```
