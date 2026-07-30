<?php

require __DIR__.'/Support/TestEnvironment.php';

use Tests\Support\TestEnvironment;

$testPassword = getenv('STUDENT_DOCUMENT_MANAGEMENT_TEST_DB_PASSWORD');

if (! is_string($testPassword) || $testPassword === '') {
    throw new RuntimeException(
        'The test database credential is unavailable in the process environment.',
    );
}

$testEnvironment = TestEnvironment::resolve($testPassword);

foreach ($testEnvironment as $name => $value) {
    putenv("{$name}={$value}");
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

require dirname(__DIR__).'/vendor/autoload.php';
