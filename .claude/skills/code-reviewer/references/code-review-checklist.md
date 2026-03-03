# Code Review Checklist

| Category           | Key Questions                                            |
| ------------------ | -------------------------------------------------------- |
| **Design**         | Does it fit existing patterns? Right abstraction level?  |
| **Logic**          | Edge cases handled? Race conditions? Null checks?        |
| **Security**       | Input validated? Auth checked? Secrets safe?             |
| **Performance**    | N+1 queries? Memory leaks? Caching needed?               |
| **Tests**          | Adequate coverage? Edge cases tested? Mocks appropriate? |
| **Naming**         | Clear, consistent, intention-revealing?                  |
| **Error Handling** | Errors caught? Meaningful messages? Logged?              |
| **Documentation**  | Public APIs documented? Complex logic explained?         |

## Context

- [] Understand the purpose and scope of the code change.
- [] Review related documentation and requirements.
- [] Read PR description and comments for context.

## Design

- [] Does the code follow established design patterns?
- [] Is the abstraction level appropriate for the task?
- [] Are there opportunities for code reuse?

## Logic

- [] Are all edge cases considered and handled?
- [] Verify error handling and exceptions.
- [] Review naming conventions for clarity and consistency.

## Security

- [] Check for potential security vulnerabilities.
- [] Ensure sensitive data is handled securely.
- [] Validate all inputs and outputs.
- [] API Resources and manual array maps never expose `$this->id` — always use `$this->resource_id` as `id`.

## Performance

- [] Check for any performance concerns.
- [] Identify and eliminate N+1 query issues.
- [] Suggest caching strategies if applicable.

## Tests

- [] Ensure adequate test coverage for new code.
- [] Verify tests cover edge cases and failure scenarios.
- [] Check that mocks and stubs are used appropriately.
- [] Ensure test names are descriptive and meaningful.

## Naming

- [] Are variable and function names clear and descriptive?
- [] Ensure naming conventions are consistent throughout the codebase.
- [] Verify that names reveal the intention behind their use.

## Error Handling

- [] Confirm that errors are caught and handled gracefully.
- [] Ensure error messages are meaningful and actionable.
- [] Check that errors are logged appropriately for debugging.

## Documentation

- [] Verify that public APIs are well-documented.
- [] Ensure complex logic is explained with comments.
- [] Check for up-to-date documentation reflecting code changes.
