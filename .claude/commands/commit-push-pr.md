---
allowed-tools: Bash(git checkout --branch:*), Bash(git add:*), Bash(git status:*), Bash(git push:*), Bash(git commit:*), Bash(gh pr create:*), Bash(gh pr view:*), Bash(./vendor/bin/sail composer run lint:fix:*), Bash(./vendor/bin/sail composer run lint:*), Bash(npm run lint:*), Bash(npm run format:*)
description: Run quality fixes, commit, push, and open a PR (or push to existing)
model: sonnet
---

## Context

- Current git status: !`git status`
- Current git diff (staged and unstaged changes): !`git diff HEAD`
- Current branch: !`git branch --show-current`
- Existing PR for this branch: !`gh pr view --json url,title,state 2>/dev/null || echo "NO_PR"`

## Your task

Follow these steps in order:

### Step 1 — Auto-fix lint issues

Run both fixers in parallel:
- `./vendor/bin/sail composer run lint:fix` — fixes PHP style issues
- `npm run lint && npm run format` — fixes JS/TS style and formatting

### Step 2 — Verify lint passes

Run `./vendor/bin/sail composer run lint` to confirm no remaining PHP style issues.
If it fails, stop and report the remaining issues to the user.

### Step 3 — Stage all changes

Run `git add -A` to stage all changes (including any auto-fixed files).

### Step 4 — Commit

- If on `main`, create a new branch first
- Create a single commit with a conventional commit message (`feat:`, `fix:`, `chore:`, etc.)

### Step 5 — Push and PR

- Push the branch to origin
- Check the "Existing PR" context above:
  - If it shows a PR URL → the PR already exists, just push. Do NOT run `gh pr create`.
  - If it shows `NO_PR` → create a pull request using `gh pr create`
