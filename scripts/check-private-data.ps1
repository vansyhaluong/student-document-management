[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference = 'SilentlyContinue'

function Stop-WithFailure {
    Write-Output 'FAIL'
    exit 1
}

function Convert-NullSeparatedOutput {
    param([string] $Value)

    if ([string]::IsNullOrEmpty($Value)) {
        return @()
    }

    return @($Value -split "`0" | Where-Object { $_ -ne '' })
}

function Test-ForbiddenRepositoryPath {
    param([string] $Path)

    $normalized = $Path.Replace('\', '/').TrimStart('./')
    $lower = $normalized.ToLowerInvariant()
    $allowedSchema = 'database/schema/student_document_management_schema.sql'

    if ($lower.EndsWith('.sql') -and $lower -ne $allowedSchema) {
        return $true
    }

    if ($lower.EndsWith('.csv')) {
        return $true
    }

    if (
        $lower.StartsWith('database/private-imports/') -or
        $lower.StartsWith('database/private-data/')
    ) {
        return $true
    }

    $fileName = [IO.Path]::GetFileName($lower)
    if (
        ($fileName -eq '.env' -or $fileName.StartsWith('.env.')) -and
        $fileName -ne '.env.example'
    ) {
        return $true
    }

    if (
        $fileName -match '\.(pem|key|p12|pfx|jks|keystore|secret)$' -or
        $fileName -match '^(id_rsa|id_ed25519)$' -or
        $fileName -match '^(credentials|secrets?)(\..+)?$'
    ) {
        return $true
    }

    return $false
}

function Test-SanitizedSqlContent {
    param([string[]] $Lines)

    $dataStatementPattern =
        '^\s*(INSERT(?:\s+IGNORE)?\s+INTO|REPLACE\s+INTO|LOAD\s+DATA)\b'

    foreach ($line in $Lines) {
        if ($line -match $dataStatementPattern) {
            return $false
        }
    }

    return $true
}

function Test-IgnoredDependencyPath {
    param([string] $Path)

    $normalized = $Path.Replace('\', '/').TrimStart('./').ToLowerInvariant()

    return (
        $normalized -eq 'vendor' -or
        $normalized.StartsWith('vendor/') -or
        $normalized -eq 'node_modules' -or
        $normalized.StartsWith('node_modules/')
    )
}

try {
    $repositoryRootOutput = & git rev-parse --show-toplevel 2>$null
    if ($LASTEXITCODE -ne 0 -or @($repositoryRootOutput).Count -ne 1) {
        Stop-WithFailure
    }

    $repositoryRootLines = @($repositoryRootOutput)
    $repositoryRoot = [IO.Path]::GetFullPath(
        [string] $repositoryRootLines[0]
    ).TrimEnd('\', '/')
    $repositoryPrefix = $repositoryRoot + [IO.Path]::DirectorySeparatorChar

    $unmergedOutput = & git diff --name-only --diff-filter=U -z 2>$null
    if ($LASTEXITCODE -ne 0) {
        Stop-WithFailure
    }

    $unmergedPaths = @(Convert-NullSeparatedOutput ($unmergedOutput -join ''))
    if ($unmergedPaths.Count -gt 0) {
        Stop-WithFailure
    }

    $trackedOutput = & git ls-files -z 2>$null
    if ($LASTEXITCODE -ne 0) {
        Stop-WithFailure
    }

    $stagedOutput = & git diff --cached --name-only --diff-filter=ACMR -z 2>$null
    if ($LASTEXITCODE -ne 0) {
        Stop-WithFailure
    }

    $untrackedOutput = & git ls-files --others --exclude-standard -z 2>$null
    if ($LASTEXITCODE -ne 0) {
        Stop-WithFailure
    }

    $ignoredOutput = & git ls-files --others --ignored --exclude-standard -z 2>$null
    if ($LASTEXITCODE -ne 0) {
        Stop-WithFailure
    }

    $filesystemPaths = @(
        Convert-NullSeparatedOutput ($untrackedOutput -join '')
        Convert-NullSeparatedOutput ($ignoredOutput -join '')
    ) | Where-Object { -not (Test-IgnoredDependencyPath $_) }

    $repositoryPaths = @(
        Convert-NullSeparatedOutput ($trackedOutput -join '')
        Convert-NullSeparatedOutput ($stagedOutput -join '')
        $filesystemPaths
    ) | Select-Object -Unique

    foreach ($path in $repositoryPaths) {
        if (Test-ForbiddenRepositoryPath $path) {
            Stop-WithFailure
        }
    }

    $sanitizedRelativePath =
        'database/schema/student_document_management_schema.sql'
    $sanitizedPath = Join-Path $repositoryRoot $sanitizedRelativePath
    if (-not (Test-Path -LiteralPath $sanitizedPath -PathType Leaf -ErrorAction Stop)) {
        Stop-WithFailure
    }

    $workingLines = Get-Content -LiteralPath $sanitizedPath -Encoding UTF8 -ErrorAction Stop
    if (-not (Test-SanitizedSqlContent $workingLines)) {
        Stop-WithFailure
    }

    & git cat-file -e ":$sanitizedRelativePath" 2>$null
    if ($LASTEXITCODE -ne 0) {
        Stop-WithFailure
    }

    $stagedSql = & git show ":$sanitizedRelativePath" 2>$null
    if ($LASTEXITCODE -ne 0) {
        Stop-WithFailure
    }

    if (-not (Test-SanitizedSqlContent @($stagedSql))) {
        Stop-WithFailure
    }

    $privateImportPath = [Environment]::GetEnvironmentVariable(
        'PRIVATE_IMPORT_PATH'
    )
    if (-not [string]::IsNullOrWhiteSpace($privateImportPath)) {
        $isFullyQualified =
            $privateImportPath -match '^(?:[A-Za-z]:[\\/]|\\\\|/)'
        if (-not $isFullyQualified) {
            Stop-WithFailure
        }

        $resolvedPrivatePath = [IO.Path]::GetFullPath($privateImportPath)
        if (
            $resolvedPrivatePath.Equals(
                $repositoryRoot,
                [StringComparison]::OrdinalIgnoreCase
            ) -or
            $resolvedPrivatePath.StartsWith(
                $repositoryPrefix,
                [StringComparison]::OrdinalIgnoreCase
            )
        ) {
            Stop-WithFailure
        }

        if (-not (Test-Path -LiteralPath $resolvedPrivatePath -PathType Leaf -ErrorAction Stop)) {
            Stop-WithFailure
        }
    }

    Write-Output 'PASS'
    exit 0
}
catch {
    Stop-WithFailure
}
