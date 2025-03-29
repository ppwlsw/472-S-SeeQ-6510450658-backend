<?php

namespace Tests\Feature;

use App\Http\Controllers\API\ShopController;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as FakerFactory;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;
    private $userRepository;
    private $shopRepository;
    private $shopController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->app->make(UserRepository::class);
        $this->shopRepository = $this->app->make(ShopRepository::class);

        $this->shopController = new ShopController(
            $this->shopRepository,
            $this->userRepository
        );
    }

    /* Todo: 1. Admin View All Customer Account
        Acceptance Criteria:
        1.Given a customer account exist in the system, when the admin views the customer list,
        then the system should display all available customers.
        2.Given no customers exist, when the admin views the customer list, then the system
        should display an appropriate message indicating no customers are available.
        3.Given a customer has been recently created or updated, when the admin refreshes the customer list,
        then the system should display the latest customer data.
    */
    public function test_admin_can_show_all_customer_correct(): void
    {
        $admin = User::factory()->create([
            'name' => "adminTest",
            'email' => "adminTest00@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000001",
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        $user1 = User::factory()->create([
            'name' => "test01",
            'email' => "test01@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000001",
            'role' => 'CUSTOMER',
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'name' => "test02",
            'email' => "test02@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000002",
            'role' => 'CUSTOMER',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->getJson('/api/users/withTrashed');

        $response->assertStatus(200);

        $response->assertJson([
            "data" => [
                [
                    'id' => $user1->id,
                    'email' => $user1->email,
                    'name' => $user1->name,
                    'role' => $user1->role,
                    'phone' => $user1->phone,
                    'image_url' => $user1->image_url,
                    'is_verified' => $user1->email_verified_at !== null,
                ],
                [
                    'id' => $user2->id,
                    'email' => $user2->email,
                    'name' => $user2->name,
                    'role' => $user2->role,
                    'phone' => $user2->phone,
                    'image_url' => $user2->image_url,
                    'is_verified' => $user2->email_verified_at !== null,
                ]
            ],
        ]);
    }

    public function test_admin_show_appropriate_message_when_customer_not_exist(): void
    {
        $admin = User::factory()->create([
            'name' => "adminTest",
            'email' => "adminTest00@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000001",
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->getJson('/api/users/withTrashed');

        $response->assertStatus(404);

        $response->assertJson([
            "message"=>  "No customer found",
        ]);

    }

    public function test_admin_show_latest_customer(): void
    {
        $admin = User::factory()->create([
            'name' => "adminTest",
            'email' => "adminTest00@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000001",
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);


        $user1 = User::factory()->create([
            'name' => "test01",
            'email' => "test01@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000001",
            'role' => 'CUSTOMER',
            'email_verified_at' => now(),
        ]);


        $this->actingAs($admin);


        $user2 = User::factory()->create([
            'name' => "test02",
            'email' => "test02@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000002",
            'role' => 'CUSTOMER',
            'email_verified_at' => now(),
        ]);


        sleep(1);
        $this->delete("/api/users/{$user2->id}");


        $response = $this->getJson('/api/users/withTrashed');


        $response->assertStatus(200);

        $response->assertJson([
            "data" => [
                [
                    'id' => $user2->id,
                    'email' => $user2->email,
                    'name' => $user2->name,
                    'role' => $user2->role,
                    'phone' => $user2->phone,
                    'image_url' => $user2->image_url ?? null,
                    'is_verified' => $user2->email_verified_at !== null,
                ]
            ],
        ]);
    }

    /* Todo: 2. Admin Create Account Shop
        Acceptance Criteria:
        1.Given the admin is logged in, when the admin provides valid shop details and submits the form,
        then the system should create the shop account successfully.
        2.Given a shop email with the same name already exists, when the admin attempts to create a duplicate shop email,
        then the system should prevent it.
        3.Given the admin provides incomplete or invalid data, when they attempt to create a shop account,
        then the system should reject the request.
    */
    public function test_admin_can_create_shop_successfully(): void
    {
        $admin = User::factory()->create([
            'name' => "adminTest",
            'email' => "adminTest00@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000001",
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $createdData = [
            'name' => 'shopTest',
            'address' => 'addressTest',
            'phone' => '0000000000',
            'email' => 'test@test.com',
            'password' => 'Password123',
            'latitude' => '13.7563',
            'longitude' => '100.5018',
        ];

        $response = $this->postJson("/api/shops", $createdData);

        $this->assertDatabaseHas('shops', [
            'name' => 'shopTest',
            'address' => 'addressTest',
            'phone' => '0000000000',
            'latitude' => '13.7563',
            'longitude' => '100.5018',
        ]);
    }

    public function test_admin_cannot_create_shop_with_invalid_data(): void
    {
        $admin = User::factory()->create([
            'name' => "adminTest",
            'email' => "adminTest00@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000001",
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $invalidData = [
            'name' => '',
            'address' => '',
            'phone' => 'invalid-phone',
            'email' => 'not-an-email',
            'password' => '123',
            'latitude' => 'abc',
            'longitude' => null,
        ];

        $response = $this->postJson("/api/shops", $invalidData);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
            'email',
            'password',
            'latitude',
        ]);
    }

    public function test_admin_cannot_create_shop_with_already_exist()
    {
        $admin = User::factory()->create([
            'name' => "adminTest",
            'email' => "adminTest00@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000001",
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $createdData = [
            'name' => 'shopTest',
            'address' => 'addressTest',
            'phone' => '0000000000',
            'email' => 'test@test.com',
            'password' => 'Password123',
            'latitude' => '13.7563',
            'longitude' => '100.5018',
        ];

        $this->postJson("/api/shops", $createdData);

        $newCreatedData = [
            'name' => 'shopTest',
            'address' => 'addressTest',
            'phone' => '0000000000',
            'email' => 'test@test.com',
            'password' => 'Password123',
            'latitude' => '13.7563',
            'longitude' => '100.5018',
        ];

        $response = $this->postJson("/api/shops", $newCreatedData);

        $response->assertStatus(422);
    }

    /* Todo: 3. Shop ยืนยันอีเมล (Verify)
        Acceptance Criteria:
        1.Given a shop account exists and has received a verification email, when the shop clicks on the verification link with a valid token,
        then the system should activate the shop account.
        2.Given the shop account does not exist in the system, when the shop clicks on the verification link,
        then the system should return a error massage User not found.
        3.Given the shop account has already been verified, when the shop clicks on the verification link again,
        then the system should reject the request.
    */
    public function test_shop_can_verify_email_correct(): void
    {
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
        ]);

        $faker = FakerFactory::create('th_TH');
        $shop = Shop::factory()->create([
            'user_id' => $user->id,
            'name' => "Shop test",
            'image_url' => $faker->imageUrl(640, 480, 'business'),
            'phone' => $faker->phoneNumber,
            'address' => $faker->address,
            'is_open' => $faker->boolean,
            'latitude' => $faker->latitude(12, 14),
            'longitude' => $faker->longitude(100, 104),
        ]);

        $user->email_verified_at = null;
        $user->save();

        $hash = sha1($user->email);
        $response = $this->get("/api/auth/emails/{$user->id}/{$hash}/verify");

        $response->assertStatus(200);
    }

    public function test_shop_account_not_exist(): void
    {

        $user_id = 9999999;
        $hash = sha1("test@test.com");

        $response = $this->get("/api/auth/emails/{$user_id}/{$hash}/verify");
        $response->assertStatus(404);

        $response->assertJson([
            'error' => 'User not found'
        ]);
    }

    public function test_shop_has_already_been_verified()
    {
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
        ]);

        $faker = FakerFactory::create('th_TH');
        $shop = Shop::factory()->create([
            'user_id' => $user->id,
            'name' => "Shop test",
            'image_url' => $faker->imageUrl(640, 480, 'business'),
            'phone' => $faker->phoneNumber,
            'address' => $faker->address,
            'is_open' => $faker->boolean,
            'latitude' => $faker->latitude(12, 14),
            'longitude' => $faker->longitude(100, 104),
        ]);

        $hash = sha1($user->email);
        $response = $this->get("/api/auth/emails/{$user->id}/{$hash}/verify");

        $response->assertStatus(400);
    }
}
