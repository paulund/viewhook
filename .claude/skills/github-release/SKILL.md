---
name: github-release
description: Create a GitHub release for the repo. Use when the user wants to publish a new release, cut a version tag, or ship a new version. Tags from main branch using semver and generates a customer-facing release description focused on features and bug fixes.
---

# GitHub Release

## Workflow

### 1. Gather context

Run these in parallel:

```bash
git fetch --tags
git tag --sort=-v:refname | head -5          # latest tags
git log <last-tag>..HEAD --oneline           # commits since last release
```

If no tags exist, treat the base version as `v0.0.0` and show all commits.

### 2. Determine the next version

Inspect commits since the last tag to propose a semver bump:

| Signal | Bump |
|---|---|
| Any `feat:` commit | minor |
| Only `fix:`, `perf:`, `chore:`, etc. | patch |
| Breaking change (`BREAKING CHANGE` in body or `!` suffix) | major |

Present the proposed next version to the user and ask them to confirm or override before proceeding.

### 3. Write the release notes

Translate the raw commits into a customer-facing description. Rules:

- **Audience**: End-users and customers, not engineers.
- **Tone**: Clear, friendly, benefit-focused. No jargon, no internal ticket numbers, no PR numbers.
- **Structure**:
  - Brief 1-sentence summary of what this release delivers.
  - `## What's new` section for features (omit if none).
  - `## Bug fixes` section for fixes (omit if none).
  - `## Improvements` section for perf/chore/refactor changes worth mentioning (omit if minor/noise).
- Skip dependency bumps, CI changes, and internal tooling commits entirely — they add no value to customers.
- Use plain bullet points, active voice, present tense ("You can now…", "Fixed an issue where…").

Show the draft release notes to the user and ask for approval before creating the release.

### 4. Create the release

Once the version and notes are approved:

```bash
# Ensure main is up to date
git checkout main && git pull origin main

# Create and push the tag, then publish the release
gh release create <version> \
  --title "<version>" \
  --notes "<approved release notes>" \
  --target main
```

Do not use `--draft` or `--prerelease` unless the user explicitly requests it.

### 5. Confirm

Print the URL of the published release so the user can view it.

## Constraints

- Always tag off `main`. Never release from a feature branch.
- Never create the release without user approval of both the version number and the release notes.
- Never include internal IDs, PR numbers, branch names, or technical implementation details in the release notes.
