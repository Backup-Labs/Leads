<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
