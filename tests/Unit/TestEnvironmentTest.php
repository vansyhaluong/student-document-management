<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\TestEnvironment;

class TestEnvironmentTest extends TestCase
{
    public function test_missing_process_overrides_are_replaced_with_the_test_target(): void
    {
        $environment = TestEnvironment::resolve(
            self::testPassword(),
            static fn (string $name): false => false,
        );

        $this->assertSame('mariadb', $environment['DB_CONNECTION']);
        $this->assertSame('127.0.0.1', $environment['DB_HOST']);
        $this->assertSame('3306', $environment['DB_PORT']);
        $this->assertSame(
            'student_document_management_test',
            $environment['DB_DATABASE'],
        );
        $this->assertSame(
            'student_document_management_test_app',
            $environment['DB_USERNAME'],
        );
    }

    public function test_matching_process_overrides_are_allowed(): void
    {
        $provided = [
            'DB_CONNECTION' => 'mariadb',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'student_document_management_test',
            'DB_USERNAME' => 'student_document_management_test_app',
        ];

        $environment = TestEnvironment::resolve(
            self::testPassword(),
            static fn (string $name): string => $provided[$name],
        );

        $this->assertSame($provided['DB_DATABASE'], $environment['DB_DATABASE']);
        $this->assertSame($provided['DB_USERNAME'], $environment['DB_USERNAME']);
    }

    public function test_development_database_override_is_rejected_without_password_disclosure(): void
    {
        $testPassword = self::testPassword();

        try {
            TestEnvironment::resolve(
                $testPassword,
                static fn (string $name): string|false => $name === 'DB_DATABASE'
                    ? 'student_document_management_dev'
                    : false,
            );

            $this->fail('A conflicting database override was accepted.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'DB_DATABASE conflicts with the required test environment.',
                $exception->getMessage(),
            );
            $this->assertStringNotContainsString(
                $testPassword,
                $exception->getMessage(),
            );
        }
    }

    public function test_development_username_override_is_rejected_without_password_disclosure(): void
    {
        $testPassword = self::testPassword();

        try {
            TestEnvironment::resolve(
                $testPassword,
                static fn (string $name): string|false => $name === 'DB_USERNAME'
                    ? 'student_document_management_dev_app'
                    : false,
            );

            $this->fail('A conflicting username override was accepted.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'DB_USERNAME conflicts with the required test environment.',
                $exception->getMessage(),
            );
            $this->assertStringNotContainsString(
                $testPassword,
                $exception->getMessage(),
            );
        }
    }

    private static function testPassword(): string
    {
        return hash('sha256', self::class);
    }
}
