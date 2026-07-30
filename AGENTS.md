# AGENTS.md

## Purpose

- This file defines execution rules for agents working in this repository.
- Keep this file concise and operational.
- Do not duplicate detailed requirements, architecture, or implementation plans here.
- Follow the referenced documents for project-specific decisions.

## 1. Sources of truth

- `docs/REQUIREMENTS.md` decides business rules and acceptance criteria.
- `docs/ARCHITECTURE.md` decides architecture and technical design.
- `docs/IMPLEMENTATION_PLAN.md` decides task order, dependencies, phase gates,
  and Decision Gate status.
- Read the relevant sections before changing code, schema, configuration, or tests.
- Do not replace an approved decision with a personal preference.
- Do not infer a missing business rule or silently resolve an open question.
- If the documents conflict, stop and report the exact conflicting passages.
- Ask for a decision instead of choosing which document to follow.

## 2. Task scope

- Work on one task ID at a time unless the user explicitly requests otherwise.
- Keep every change within the current task's stated output and acceptance criteria.
- Do not expand scope, perform unrelated refactors, or begin the next task.
- Do not implement a task whose dependencies are incomplete.
- Do not implement a task that is blocked by a phase gate or Decision Gate.
- Do not start P1 until P0-01 through P0-04 have documented evidence of completion.
- DG-005 blocks all of P9 and production sign-off.
- A request to inspect, review, or diagnose does not authorize implementation.
- Stop when the requested task is complete and wait for the next instruction.

## 3. Preflight

- Check the current Git branch.
- Check `git status` before editing.
- Read the requirement, architecture, and plan sections relevant to the task.
- Identify the task ID, dependencies, gates, and acceptance criteria.
- Verify that the required environment and inputs are available.
- State the files expected to change before making edits.
- Compare expected files with the allowed scope.
- Stop if the working tree contains unexplained out-of-scope changes.
- Do not overwrite or absorb existing user changes without confirmation.
- Report a missing dependency or gate as a blocker.

## 4. Backend architecture

- Use the request path:
  `HTTP Request → Route/Middleware → Form Request → Controller → Service
  → Repository → Model`.
- Controllers must not call Models or the database directly.
- Controllers should coordinate input and responses, not contain business rules.
- Form Requests perform validation and authorization for HTTP input.
- Services own business rules, state transitions, and business transactions.
- Repositories are the persistence boundary.
- Services must not call Eloquent or Query Builder directly.
- Keep transaction boundaries in the owning Service.
- Keep state-transition enforcement authoritative on the server.
- Do not create a generic `BaseRepository`.
- Do not create a generic `CommonService`.

## 5. Frontend

- Use React, TypeScript, and Inertia for authenticated internal areas.
- Use Blade for public pages, error pages, and the Inertia shell.
- Server-side validation and authorization are authoritative.
- Client-side checks may improve UX but never replace server enforcement.
- Do not create a JSON API solely to serve Inertia pages.
- Do not pass raw Models to frontend pages.
- Use explicit, minimal page props and response allowlists.
- Do not expose credentials, secrets, internal fields, or sensitive data.
- Keep business decisions out of React components.

## 6. Database

- Use MariaDB 10.11 for development, test/CI, and the production target.
- Treat the MariaDB 10.4 baseline only as an import source.
- Keep development and test databases separate.
- Never run destructive test setup against development or production data.
- Do not substitute SQLite for MariaDB migration or integration behavior.
- Test constraints, transactions, and locking on MariaDB.
- Preserve approved constraint, index, foreign-key, and transaction behavior.
- Follow the session and idempotency decisions in `docs/ARCHITECTURE.md`.
- Use migrations for schema changes; do not patch production schema manually.
- Keep real import data outside the repository and CI.

## 7. Security and data

- Follow the approved Argon2id and password policy.
- Do not change authentication behavior without an approved requirement.
- Do not change Public Lookup verification or output rules without approval.
- Never log passwords or temporary passwords.
- Never log session cookies, credentials, or security tokens.
- Never log raw idempotency tokens.
- Do not commit `.env` files or secrets.
- Do not commit private SQL, real student data, or private import files.
- CI may use only sanitized schema and fake fixtures.
- Keep authorization checks on the server for every protected action.
- Preserve generic authentication failure messages where required.

## 8. Testing

- Every business behavior change requires an appropriate test.
- Claim `PASS` only for a command that was actually run successfully.
- Do not imply unexecuted checks passed.
- Test constraints, migrations, transactions, and locking on MariaDB.
- Add regression coverage for corrected defects.
- Run the smallest relevant checks during development.
- Run all task-required quality gates before completion.
- Run `git diff --check` before reporting completion.
- Obtain concrete build and test commands from the repository after scaffolding.
- Do not invent commands that the repository does not define.
- Report skipped or unavailable checks with the reason.

## 9. Git safety

- Do not work directly on `main` or `develop`.
- Do not commit, push, create a PR, merge, or deploy without an explicit request.
- Do not run commands that destructively reset or clean the working tree.
- Do not modify, delete, or revert changes outside the current task.
- Preserve unrelated tracked and untracked files.
- Stage only files belonging to the current task.
- Each commit must contain only files belonging to the current task.
- Inspect the staged diff before committing.
- Use a concise commit message that describes the task outcome.

## 10. Completion report

- Report the task ID and implemented scope.
- List every changed file.
- Summarize the behavior implemented.
- List commands and checks that were actually run.
- Report the result of each executed check.
- List checks not run and explain why.
- Report remaining blockers and Decision Gates.
- Report the final `git status`.
- Do not declare completion while any exit criterion is unmet.
- Do not continue into another task after the completion report.
