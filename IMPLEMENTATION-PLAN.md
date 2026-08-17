# IMPLEMENTATION PLAN

## Source of Truth

1. `docs/REQUIREMENT.md` — business scope.
2. `docs/ARCHITECHTURE.md` — implementation architecture.
3. The approved real MariaDB `doctrack` schema — data model.
4. This plan — phase order and task scope.
5. Existing source — current conventions.

Before each database-dependent task, inspect the live approved schema and map
code to its actual tables, columns, datatypes, keys, indexes, and relationships.
Do not change schema unless explicitly requested.

## Approved Decisions

- Roles: `ADMIN`, `SECRETARY`, `EMPLOYEE`; persistence values:
  `admin`, `secretary`, `staff`.
- Main aggregate: `student_documents`, linked to `students` and
  `document_types`.
- `assigned_secretary_user_id` is the legacy responsible-user column and may
  reference all three roles.
- Use the seven-status workflow approved in Architecture.
- Use custom Service/Repository access to `activity_log`; add no audit package.
- Preserve bcrypt compatibility.
- Attachment, XLSX/PDF, and email are outside MVP.

## Phase 0 — Decisions and Schema Understanding — COMPLETE

- Requirement and Architecture are approved.
- Roles, workflow, assignment, audit, password, and MVP scope are approved.
- `doctrack` is the authoritative existing schema.
- Major Requirement/database conflicts have been resolved.

Phase 1 may begin when requested. No additional infrastructure, privacy,
production, or schema-baseline gate blocks application coding.

---

## Phase 1 — Application Foundation

### 1. Schema-Aware Domain and Persistence Foundation

- Inspect `doctrack` and map Models for `users`, `students`, `document_types`,
  `document_statuses`, `student_documents`, `document_status_history`, and
  `activity_log`.
- Add approved role/status enums and persistence mapping.
- Add use-case Repository contracts and Eloquent implementations.
- Bind repositories through the service container.
- Do not create migrations or unsupported fields.

Validation:

- Application connects to MariaDB locally.
- Model table/column/cast/relationship mappings match the actual schema.
- Repository queries work against existing data without modifying schema.

### 2. Shared HTTP and Error Infrastructure

- Configure centralized HTML/JSON exception handling.
- Add standardized JSON response/pagination helpers only for actual JSON
  endpoints.
- Establish simple DTO/filter conventions needed by planned modules.
- Keep Controllers thin and transactions in Services.

Validation:

- Validation, unauthorized, not-found, business, and system failures return the
  correct Blade or JSON response without exposing internals.
- No Controller database query or generic `BaseRepository/CommonService`.

---

## Phase 2 — Authentication and Application Shell

### 3. Authentication and Authorization

Depends on: Phase 1.

- Implement username/password login, logout, and current session user.
- Map `password_hash`, verify bcrypt, and enforce active accounts.
- Implement middleware, Policies, and responsible-user query scopes for the
  three approved roles.
- Audit login/security actions without email flows.

Validation:

- Valid users can log in locally; invalid/inactive users are rejected.
- Unauthorized role/resource access is rejected by backend.
- Employee only sees assigned documents.
- Password, hash, session, and secret values are not exposed.

### 4. Blade Application Shell

- Build responsive sidebar, topbar, breadcrumb, and main layout.
- Add shared flash/error, field, status badge, table, and pagination components.
- Present role-aware menus and loading/empty/error states.

Validation:

- Shell works for all roles.
- CSRF and field-level errors work.
- Hidden UI actions remain protected by backend authorization.

---

## Phase 3 — Users and Document Types

### 5. User Management

Depends on: Authentication.

- Implement Admin-only list/search/create/update, role assignment,
  lock/unlock, and internal password reset.
- Use exact `users` columns and `staff ↔ EMPLOYEE` mapping.
- Record required audit events.

Validation:

- Only Admin accesses the module.
- Only approved roles can be assigned.
- Password hashes are never returned or logged.
- No email workflow is introduced.

### 6. Document Type Management

- Implement Admin-only list/create/update and active/inactive control.
- Prevent deletion of referenced types.
- Keep inactive types visible on existing documents but unavailable for new
  document selection.

Validation:

- Existing relationships remain valid.
- Secretary and Employee cannot mutate document types.

---

## Phase 4 — Student Documents and Workflow

### 7. Student Document Management

Depends on: Users and Document Types.

- Implement list/detail/create/update using actual `student_documents` fields
  and existing students.
- Search by document code, student code, or student name.
- Filter by type, status, responsible user, and submitted date; support
  allowlisted sort and pagination.
- Apply record access scope before query results are returned.
- Preserve unique/immutable document code and audit important changes.
- Build Blade list/detail/form and required UI states.

Validation:

- Admin/Secretary can create; Employee cannot create and only accesses assigned
  documents.
- No unsupported field or attachment feature is introduced.
- Search/filter/pagination do not leak inaccessible records.
- CRUD flow works against the existing local database.

### 8. Assignment, Acceptance, and Status History

Depends on: Student Document Management.

- Assign any approved role through `assigned_secretary_user_id`.
- Implement Employee acceptance of assigned `waiting_for_receipt` documents.
- Enforce the approved status transition map and terminal states in Service.
- Use transaction and row lock for assignment, acceptance, and status change.
- Maintain `completed_at`/`invalid_reason` rules and append history + audit
  atomically.

Validation:

- Invalid or unauthorized transitions fail without partial writes.
- Concurrent operations do not create contradictory assignment/status state.
- New history uses only approved status codes and cannot be edited directly.

---

## Phase 5 — Dashboard, Reports, and Audit

### 9. Dashboard and Reports

Depends on: Student Document Workflow.

- Implement counts and aggregates by status, type, responsible user,
  submitted date, and completed date.
- Apply role/access scope before aggregate queries.
- Implement date/type/status filters and Blade states.
- Do not implement XLSX/PDF.

Validation:

- Counts match database queries and each role's visible records.
- Filters are validated and perform acceptably on current local data.

### 10. Audit Log

- Implement `ActivityLogService`/`ActivityLogRepository` against
  `activity_log` without a new dependency.
- Cover required business/security actions.
- Implement Admin-only read/filter/detail views; no update/delete flow.
- Use stable event codes and allowlisted/masked properties for new entries.

Validation:

- Critical actions create traceable audit events.
- Sensitive values are not stored.
- Only Admin can view; no user can edit/delete audit entries.

---

## Phase 6 — Application Validation

### 11. Functional and Automated Validation

- Test authentication, active accounts, roles, and record scope.
- Test validation, user/type/document flows, search/filter/pagination,
  assignment/acceptance, workflow/history, and audit.
- Test relevant transaction rollback and centralized error behavior.
- Run targeted Laravel tests and broader tests when useful.
- Run Laravel Pint and `npm run build` where applicable.
- Manually verify database connectivity and affected local CRUD/business flows.

Completion criteria:

- Requested functionality works in the local development environment.
- Backend authorization protects every implemented action.
- Code follows the required layers and actual database schema.
- No unauthorized schema/dependency/role/status/email/attachment change exists.
- Unrelated pre-existing issues are reported as warnings, not treated as task
  failures.

## Non-Blocking Notes

- Development/test schema alignment, privacy tooling, dump placement, CI/CD,
  production accounts, and MariaDB root hardening may be improved separately.
- Legacy data includes some inconsistent status/audit values; report them when
  they affect the module being implemented, but do not rewrite live data unless
  explicitly requested.
- These notes do not block Phase 1 or ordinary application implementation.
