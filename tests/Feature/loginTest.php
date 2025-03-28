<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class loginTest extends TestCase
{
    use refreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    /*
    TODO: 1. User Story Admin Login
        Acceptance Criteria:
            Given user in database which has
                email: admin@admin.com
                password: password
            1. When login with
                email: admin@admin.com
                password: password
               Then login success
            2. When login with
                email: admin@admin.com
                password: password123
               Then login not success
            3. When login with
                email: admin@admin.com
                password: password
               Then user's role is admin
    */

    public function test_admin_can_login_successfully()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'admin@admin.com',
            'password' => 'password'
        ]);
        $response->assertStatus(201);
    }
}
