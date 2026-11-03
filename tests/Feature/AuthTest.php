<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_workspace_is_automatically_initialized(): void
    {
        $response = $this->post('/register', [
            'name' => 'Paul Kagame',
            'email' => 'paul@test.rw',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'organization_name' => 'Vision 2050 Enterprise',
            'currency' => 'RWF',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'paul@test.rw')->first();
        $this->assertNotNull($user);

        $org = Organization::where('name', 'Vision 2050 Enterprise')->first();
        $this->assertNotNull($org);

        // Verify default stages and sources initialized
        $this->assertEquals(6, $org->pipelineStages()->count());
        $this->assertEquals(7, $org->leadSources()->count());
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@test.rw',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'login@test.rw',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
