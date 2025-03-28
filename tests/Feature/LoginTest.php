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
    TODO: 1. User Story: Admin Login
        Acceptance Criteria:
            Given user in database which has
                email: admin@admin.com
                password: password
            1. When admin login with
                email: admin@admin.com
                password: password
               Then login success
            2. When admin login with
                email: admin@admin.com
                password: password123
                that wrong authenticate
               Then login not success
            3. When admin login with
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

    public function test_admin_can_not_login_successfully_with_wrong_password()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'admin@admin.com',
            'password' => 'password123'
        ]);
        $response->assertStatus(401);
    }

    public function test_admin_login_as_role_admin()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'admin@admin.com',
            'password' => 'password'
        ]);

        $data = $response->json()['data'];
        $this->assertEquals("ADMIN", $data['role']);
    }


    /*
   TODO: 2. User Story: Customer Login
       Acceptance Criteria:
           Given user in database which has
               customer1:
                    name: user1
                    email: user1@gmail.com
                    image_url: http://picture/user1
                    password: password
               customer2
                    name: user2
                    email: user2@gmail.com
                    image_url: http://picture/user2
           1. When customer login with
               email: user1@gmail.com
               password: password
              Then login success and be customer
           2. When customer login with
               email: user1
               password: password
               that wrong email format
              Then login not success
           3. When customer login with google authenticate
               name: user2
               email: user2@gmail.com
               image_url: http://picture/user2
              Then login success
   */

    public function test_customer_login_successfully()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'user1@gmail.com',
            'password' => 'password'
        ]);
        $data = $response->json()['data'];
        $this->assertEquals("CUSTOMER", $data['role']);
    }

    public function test_customer_can_not_login_successfully_with_wrong_email_format()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'user1',
            'password' => 'password'
        ]);
        $response->assertStatus(422);
    }

    public function test_customer_login_successfully_with_google_authentication()
    {
        $response = $this->post('/api/auth/google/login', [
            'name' => 'user2',
            'email' => 'user2@gmail.com',
            'image_url' => 'http://picture/user2'
        ]);
        $response->assertStatus(201);
    }

    /*
   TODO: 3. User Story: Shop Login
       Acceptance Criteria:
           Given user in database which has
               shop:
                    email: starbuck@gmail.com
                    password: password
           1. When shop with
               email:  starbuck@gmail.com
               password:  password
              Then login success and be shop
           2. When shop login with
               email: starbuc@gmail.com
               password: password
               that is not exist email
              Then not login
           3. When customer login with
                email: startbuck@gmail
                password: password
                that wrong email format
              Then login not success
   */

    public function test_shop_login_successfully()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'starbuck@gmail.com',
            'password' => 'password'
        ]);
        $data = $response->json()['data'];
        $this->assertEquals("SHOP", $data['role']);
    }

    public function test_shop_can_not_login_successfully_with_no_existing_email()
    {
       $response = $this->post('/api/auth/login', [
           'email' => 'starbuc@gmail.com',
           'password' => 'password'
       ]);
       $response->assertStatus(404);
    }

    public  function test_shop_can_not_login_successfully_with_wrong_email_format()
    {
       $response = $this->post('/api/auth/login', [
           'email' => 'starbuck@.com',
           'password' => 'password'
       ]);
       $response->assertStatus(422);
    }


}
