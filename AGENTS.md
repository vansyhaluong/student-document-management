# AGENTS.md

## Purpose

Operational rules for coding agents in this repository:

`Read task → Inspect relevant code/database → Implement → Test → Report → Stop`

This is a small Laravel application for internal faculty use. Prioritize correct
behavior against the existing MariaDB database and successful local execution.

## Source of Truth

Read the relevant sections before coding:

1. `docs/REQUIREMENT.md` — business scope and rules.
2. `docs/ARCHITECHTURE.md` — architecture and technical conventions.
3. The currently approved real MariaDB schema — actual data model.
4. `IMPLEMENTATION-PLAN.md` — phase order and task scope.
5. Existing source code — current conventions and implementation.

If a real conflict makes the requested feature impossible, use the STOP rule.
Do not duplicate or rewrite source-of-truth documents during ordinary coding.

## Current Project

- Laravel 13, PHP 8.3, Blade, Tailwind CSS 4, Vite 8.
- MariaDB 10.11 with the approved `doctrack` schema.
- Session authentication using username/password.
- PHPUnit 12 and Laravel Pint.
- No standalone API specification; JSON conventions are in the Requirement and
  Architecture.
- Roles are only `ADMIN`, `SECRETARY`, and `EMPLOYEE`; database `staff` maps to
  `EMPLOYEE`.
- No email functionality.

Approved P0-01 business/database decisions are complete. Phase 1 may begin when
requested.

## Scope Rules

- Implement only the requested phase/task; do not continue automatically.
- Do not invent entities, fields, relationships, roles, statuses, transitions,
  permissions, or important business rules.
- Do not add a dependency without approval.
- Do not modify the database schema unless explicitly requested.
- Do not add tables, columns, relationships, indexes, or migrations because the
  code expects a different schema.
- Do not implement email, attachment, XLSX/PDF, or other out-of-MVP features.
- Search existing code before creating a class, component, helper, Service,
  Repository, utility, or abstraction.
- Reuse suitable code and follow the current layout, naming, style, and
  dependencies.
- Do not create generic `BaseRepository`, `CommonService`, or speculative
  abstractions.
- Do not perform unrelated refactoring.

Git cleanliness, CI/CD readiness, privacy guard status, sanitized schema
artifacts, production hardening, database-root configuration, and dump placement
are not implementation gates. Mention relevant pre-existing concerns as
warnings without expanding task scope.

## Required Architecture

Use this path:

`Route/Middleware → Form Request → Controller → Service → Repository → Model`

- Route/Middleware: routing, authentication, and coarse access checks.
- Form Request: input validation, authorization, and light normalization.
- Controller: thin HTTP coordination; calls Service and returns the response.
- Service: business rules, workflow, orchestration, and transaction boundaries.
- Repository: all Eloquent/Query Builder access, search, filters, pagination,
  aggregates, and locks.
- Model: existing-table mapping, relationships, casts, and fillable/hidden data.
- Policy/query scope: backend authorization and record visibility.
- Central exception handling: render consistent HTML/JSON failures.

Controllers must not query the database, call Models directly, or contain major
business logic. Blade visibility is UX only; backend authorization is final.
Use Service transactions for atomic multi-write operations.

Repository contracts belong in `App\Repositories\Contracts`; Eloquent/Query
Builder implementations belong in `App\Repositories\Eloquent`.

## Database Rules

The currently approved real schema is authoritative. Before implementing a
database-dependent module:

1. Inspect the actual schema.
2. Confirm table names.
3. Confirm columns and datatypes.
4. Confirm foreign keys, constraints, indexes, triggers, and relationships
   relevant to the task.
5. Map Models and Repositories to that schema.

Do not change schema or live data unless the task explicitly authorizes it. If a
requirement genuinely cannot be implemented with the existing schema, STOP and
report that specific incompatibility.

Never run destructive database commands without explicit approval. Never print
credentials or expose passwords, hashes, tokens, session payloads, secrets, or
personal data. Use the current local `.env`/MariaDB configuration needed for the
application to connect; do not change credentials unless connection work
requires it.

## Authentication, API, and UI

- Enforce authentication, active-account checks, roles, and resource scope in
  the backend.
- Map `admin`, `secretary`, and `staff` only to the three approved domain roles.
- Verify existing bcrypt hashes through Laravel's Hash contract.
- Do not implement email verification or password reset by email.
- Blade forms use CSRF, redirects, flash messages, and field errors.
- Do not create a JSON API solely for Blade pages.
- Real JSON endpoints use standardized success/error/validation/pagination
  responses and appropriate HTTP status codes.
- Do not expose stack traces, SQL errors, credentials, or internal server data.

## Implementation Workflow

1. Read the requested task in `IMPLEMENTATION-PLAN.md`.
2. Read related Requirement and Architecture sections.
3. Inspect relevant source, schema, configuration, dependencies, and tests.
4. Implement only the requested scope using the required layers.
5. Add/update useful tests for business behavior, authorization, validation,
   transactions, and regressions.
6. Run applicable checks and manually verify the affected flow when practical.
7. Report actual results and warnings.
8. Stop; do not begin the next phase.

## Validation

Focus on whether the changed application behavior works. Use applicable checks:

```powershell
# Targeted or full Laravel tests
php artisan test --filter=<TestName>
composer test

# PHP format check
vendor\bin\pint.bat --test

# Frontend build
npm run build

# Diff whitespace check
git diff --check
```

Also verify database connectivity and the actual CRUD/business flow when
relevant. There is no npm lint/type-check script; do not invent one.

Run only applicable checks. Unrelated pre-existing failures may be reported as
warnings. A working task may be `PASS WITH WARNINGS`. Never claim a command
passed if it was not run successfully.

## STOP Rule

STOP only when continuing requires one of these:

1. Inventing an important business rule.
2. Changing the existing database schema without approval.
3. Adding a dependency without approval.
4. Introducing a role, status, or workflow not defined by the project.
5. Making a significant architecture change.
6. Working around a real Requirement/Architecture/database conflict that makes
   the requested feature impossible to implement correctly.
7. Performing destructive database operations.

Do not STOP for unrelated Git state, missing CI/privacy artifacts, optional
validation failures, production hardening, database-root configuration, or
other unrelated pre-existing warnings.

When stopping, report:

```text
ABNORMALITY DETECTED

What was found:
...

Why it blocks the requested task:
...

Related files/schema:
...

Possible options:
A. ...
B. ...

Recommended option:
...

No related change has been executed.
Please confirm how to proceed.
```

## Completion Report

```md
## IMPLEMENTATION RESULT

### Status
PASS / PASS WITH WARNINGS / BLOCKED

### Implemented
- ...

### Files Changed
- ...

### Validation
- command — result

### Database Changes
- None / explicitly approved changes

### Warnings
- ...

### Remaining
- ...
```
