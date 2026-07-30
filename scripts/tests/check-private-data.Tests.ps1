[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$guardPath = [IO.Path]::GetFullPath(
    (Join-Path $PSScriptRoot '..\check-private-data.ps1')
)
$powershellExe = Join-Path $PSHOME 'powershell.exe'
$temporaryRoot = [IO.Path]::GetFullPath([IO.Path]::GetTempPath()).TrimEnd('\', '/')
$testRoots = [Collections.Generic.List[string]]::new()

function Invoke-Git {
    param(
        [string] $Repository,
        [string[]] $Arguments
    )

    & git -C $Repository @Arguments 2>$null | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw 'Git test setup failed.'
    }
}

function New-GuardTestRepository {
    $name = 'student-document-management-guard-test-' + [guid]::NewGuid().ToString('N')
    $repository = Join-Path $temporaryRoot $name
    New-Item -ItemType Directory -Path $repository | Out-Null
    $testRoots.Add($repository)

    New-Item -ItemType Directory -Path (Join-Path $repository 'database\schema') -Force | Out-Null
    [IO.File]::WriteAllText(
        (Join-Path $repository '.gitignore'),
        "/vendor/`n/node_modules/`n",
        [Text.UTF8Encoding]::new($false)
    )
    [IO.File]::WriteAllText(
        (Join-Path $repository 'database\schema\student_document_management_schema.sql'),
        "CREATE TABLE example (id BIGINT PRIMARY KEY);`n",
        [Text.UTF8Encoding]::new($false)
    )

    Invoke-Git $repository @('init', '--quiet')
    Invoke-Git $repository @('config', 'user.name', 'Guard Regression Test')
    Invoke-Git $repository @('config', 'user.email', 'guard-test@example.invalid')
    Invoke-Git $repository @('config', 'core.autocrlf', 'false')
    Invoke-Git $repository @('add', '.gitignore', 'database/schema/student_document_management_schema.sql')
    Invoke-Git $repository @('commit', '--quiet', '-m', 'test: baseline')

    return $repository
}

function Invoke-Guard {
    param([string] $Repository)

    Push-Location $Repository
    try {
        $output = @(
            & $powershellExe -NoProfile -ExecutionPolicy Bypass -File $guardPath 2>&1
        )
        return [pscustomobject]@{
            ExitCode = $LASTEXITCODE
            Output = ($output -join "`n").Trim()
        }
    }
    finally {
        Pop-Location
    }
}

function Assert-GuardResult {
    param(
        [string] $Case,
        [object] $Result,
        [int] $ExpectedExitCode,
        [string] $ExpectedOutput
    )

    if (
        $Result.ExitCode -ne $ExpectedExitCode -or
        $Result.Output -ne $ExpectedOutput
    ) {
        throw "Guard regression failed: $Case"
    }
}

try {
    $ignoredRepository = New-GuardTestRepository
    New-Item -ItemType Directory -Path (Join-Path $ignoredRepository 'vendor\package') -Force | Out-Null
    New-Item -ItemType Directory -Path (Join-Path $ignoredRepository 'node_modules\package') -Force | Out-Null
    [IO.File]::WriteAllText(
        (Join-Path $ignoredRepository 'vendor\package\Secret.php'),
        "<?php`n",
        [Text.UTF8Encoding]::new($false)
    )
    [IO.File]::WriteAllText(
        (Join-Path $ignoredRepository 'node_modules\package\Secret.php'),
        "<?php`n",
        [Text.UTF8Encoding]::new($false)
    )
    Assert-GuardResult 'ignored dependency Secret.php' (Invoke-Guard $ignoredRepository) 0 'PASS'

    $sourceRepository = New-GuardTestRepository
    New-Item -ItemType Directory -Path (Join-Path $sourceRepository 'src') -Force | Out-Null
    [IO.File]::WriteAllText(
        (Join-Path $sourceRepository 'src\Secret.php'),
        "<?php`n",
        [Text.UTF8Encoding]::new($false)
    )
    Invoke-Git $sourceRepository @('add', 'src/Secret.php')
    Assert-GuardResult 'staged source Secret.php' (Invoke-Guard $sourceRepository) 1 'FAIL'

    $trackedDependencyRepository = New-GuardTestRepository
    New-Item -ItemType Directory -Path (Join-Path $trackedDependencyRepository 'vendor\package') -Force | Out-Null
    [IO.File]::WriteAllText(
        (Join-Path $trackedDependencyRepository 'vendor\package\Secret.php'),
        "<?php`n",
        [Text.UTF8Encoding]::new($false)
    )
    Invoke-Git $trackedDependencyRepository @('add', '--force', 'vendor/package/Secret.php')
    Assert-GuardResult 'staged dependency Secret.php' (Invoke-Guard $trackedDependencyRepository) 1 'FAIL'

    Write-Output 'PASS'
    exit 0
}
catch {
    Write-Output ('FAIL: ' + $_.Exception.Message)
    exit 1
}
finally {
    foreach ($testRoot in $testRoots) {
        $resolved = [IO.Path]::GetFullPath($testRoot)
        $prefix = $temporaryRoot + [IO.Path]::DirectorySeparatorChar
        if (
            $resolved.StartsWith($prefix, [StringComparison]::OrdinalIgnoreCase) -and
            [IO.Path]::GetFileName($resolved).StartsWith(
                'student-document-management-guard-test-',
                [StringComparison]::OrdinalIgnoreCase
            )
        ) {
            Remove-Item -LiteralPath $resolved -Recurse -Force -ErrorAction SilentlyContinue
        }
    }
}