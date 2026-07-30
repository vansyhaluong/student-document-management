# Private data and import policy

## Purpose

This policy keeps real Student data, private import sources, credentials, and
PII outside Git, automated tests, CI, caches, logs, and artifacts. It applies
to local administration, P2-07 private import work, CI introduced in P1-06,
and the P9 migration rehearsal.

## Private import source

- A private import source must be stored outside the repository root.
- The runtime path is provided only through `PRIVATE_IMPORT_PATH`.
- `PRIVATE_IMPORT_PATH` must be an absolute path to an existing file.
- The resolved path must remain outside the repository root.
- There is no default, repository-relative fallback, or automatic machine
  search for a private source.
- The actual path must not be hard-coded in source, configuration examples,
  documentation, evidence, logs, command output, or artifacts.
- A private source must never be committed, pushed, uploaded, cached, or
  attached to a GitHub issue, pull request, workflow, or release.

CI must leave `PRIVATE_IMPORT_PATH` unset. P2-07 may read it only during an
explicitly authorized administrative import outside CI.

## Repository allowlist and deny rules

SQL, CSV, private-import directories, and private-data directories are denied
by default. The only version-controlled SQL allowlist entry is:

```text
database/schema/student_document_management_schema.sql
```

That file is schema-only and must not contain `INSERT`, `REPLACE`, or
`LOAD DATA`. `.env.example` is allowed; `.env`, environment-specific `.env.*`
files, credentials, private keys, certificate key stores, and secret files are
not allowed.

## Guard

Run the repository guard before committing:

```powershell
pwsh -File scripts/check-private-data.ps1
```

The guard checks repository working-tree paths, tracked files, staged files,
ignored private-data patterns, the exact sanitized SQL allowlist, and the
optional `PRIVATE_IMPORT_PATH`. It prints only `PASS` or `FAIL`; it does not
print paths, credentials, SQL row values, or PII.

P1-06 must invoke this same script in CI. CI runs it without
`PRIVATE_IMPORT_PATH` and must fail if a private artifact is tracked, staged,
present in the checkout, or added to the sanitized baseline. No separate
private-data CI workflow is created during P0-04.

## Test and CI data

- Automated tests and CI use only the sanitized schema and intentionally fake
  factories or fixtures.
- Real Student rows, snapshots, hashes derived from PII, and private import
  subsets are not test fixtures.
- CI must not download a private source or receive its path or credentials
  through secrets.
- CI caches, logs, test reports, database dumps, screenshots, and artifacts
  must not contain PII or private import content.
- Failure output must identify only PASS/FAIL or a safe category; it must not
  echo a private path, row value, credential, or SQL payload.

## Temporary files and local processing

- Avoid temporary copies when an operation can stream from the authorized
  private source.
- If a temporary file is unavoidable, create it outside the repository in an
  access-controlled private location.
- Record an owner and deletion deadline before creating a temporary copy.
- Delete temporary files with an explicit, verified path immediately after
  the authorized operation.
- Stop and request review if deletion fails or if an unexpected duplicate is
  found. Do not broaden a cleanup command or delete additional files
  automatically.
- Evidence records deletion success without recording the real path or PII.

## P9 migration rehearsal

P9 rehearsal follows this policy without exception:

- use a private, access-controlled environment outside CI;
- assign an owner and retention/deletion deadline;
- do not upload database copies, logs, caches, or artifacts containing PII;
- verify backup, restore, migration, and rollback only within that environment;
- securely delete every rehearsal copy by its verified explicit path;
- retain only non-sensitive PASS/FAIL evidence and deletion confirmation.

Any policy failure blocks the import, commit, CI run, rehearsal, or release
step that encountered it.
