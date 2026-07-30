<?php

namespace Tests\Support;

use RuntimeException;

final class TestEnvironment
{
    private const DATABASE_TARGET = [
        'DB_CONNECTION' => 'mariadb',
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '3306',
        'DB_DATABASE' => 'student_document_management_test',
        'DB_USERNAME' => 'student_document_management_test_app',
    ];

    /**
     * @param  (callable(string): (string|false))|null  $readEnvironment
     * @return array<string, string>
     */
    public static function resolve(
        string $testPassword,
        ?callable $readEnvironment = null,
    ): array {
        $readEnvironment ??= static fn (string $name): string|false => getenv($name);

        foreach (self::DATABASE_TARGET as $name => $expectedValue) {
            $providedValue = $readEnvironment($name);

            if ($providedValue !== false && $providedValue !== $expectedValue) {
                throw new RuntimeException(
                    "{$name} conflicts with the required test environment.",
                );
            }
        }

        return [
            'APP_ENV' => 'testing',
            ...self::DATABASE_TARGET,
            'DB_PASSWORD' => $testPassword,
            'DB_URL' => '',
            'DB_TIMEZONE' => '+00:00',
        ];
    }
}
