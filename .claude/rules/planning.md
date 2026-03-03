# Planning Rule

When Claude exits Plan Mode, the **first action** must be to create a structured plan file at `plans/<feature>/plan.md`.

## Trigger

This rule applies whenever Plan Mode is used for a feature that will be implemented. After the user approves the plan and Claude exits Plan Mode, Claude must write the plan file before any other action.

## Plan File Template

The plan file at `plans/<feature>/plan.md` must contain these sections:

```markdown
# Feature: <Feature Name>

## Description

<1-2 paragraphs describing what users will see and why this feature matters.>

## Acceptance Criteria

<Numbered list of testable requirements. Each criterion should be verifiable by a test.>

1. ...
2. ...

## Technical Approach

### Files to Create

| File | Purpose |
|---|---|
| ... | ... |

### Files to Modify

| File | Change |
|---|---|
| ... | ... |

### Patterns to Follow

<Reference existing patterns from the codebase. Link to relevant rules or skill references.>

## Testing Strategy

### Backend

- Feature tests: <what entry points to test>
- Unit tests: <what complex logic needs isolated testing>

### Frontend

- Component tests: <what user interactions to test>
- Integration tests: <what Inertia flows to verify>

## Risks & Trade-offs

<Bullet list of things that could go wrong, performance concerns, or alternative approaches considered and rejected.>
```

## Naming Convention

The `<feature>` directory name should be kebab-case, descriptive, and short. Examples:
- `plans/team-invitations/plan.md`
- `plans/custom-slugs/plan.md`
- `plans/export-csv/plan.md`

## Guidelines

- Keep acceptance criteria **testable** — agents use them to verify completeness
- In "Technical Approach", reference existing patterns (e.g., "Follow the CaptureRequestAction pattern for the new action")
- In "Files to Create/Modify", be specific about file paths
- The plan is the **single source of truth** for the `/implement` skill — agents read this file, not the conversation
