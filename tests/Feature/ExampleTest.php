<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_public_homepage_is_available_to_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Tra cứu và nộp hồ sơ sinh viên')
            ->assertSee('Tra cứu hồ sơ')
            ->assertSee('Mã số sinh viên (MSSV)')
            ->assertSee('Nộp hồ sơ')
            ->assertSee(route('login'), escape: false);
    }
}
