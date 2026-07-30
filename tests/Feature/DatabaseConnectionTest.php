<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function test_testing_connection_uses_the_dedicated_account_with_required_session_settings(): void
    {
        $state = DB::selectOne(<<<'SQL'
            SELECT
                DATABASE() AS database_name,
                CURRENT_USER() AS authenticated_user,
                @@SESSION.time_zone AS session_time_zone,
                @@SESSION.sql_mode AS session_sql_mode
            SQL);

        $this->assertSame('student_document_management_test', $state->database_name);
        $this->assertSame(
            'student_document_management_test_app@127.0.0.1',
            $state->authenticated_user,
        );
        $this->assertSame('+00:00', $state->session_time_zone);
        $this->assertStringContainsString(
            'STRICT_TRANS_TABLES',
            $state->session_sql_mode,
        );
    }
}
