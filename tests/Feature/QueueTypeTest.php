<?php

namespace Tests\Feature;

use App\Http\Controllers\API\QueueController;
use App\Models\Queue;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\QueueRepository;
use App\Repositories\UserQueueRepository;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;


class QueueTypeTest extends TestCase
{
    use RefreshDatabase;
    private $queueRepository;
    private $userQueueRepository;
    private $queueController;

    protected function setUp(): void
    {
        parent::setUp();

        // Use real repositories with database interactions
        $this->queueRepository = $this->app->make(QueueRepository::class);
        $this->userQueueRepository = $this->app->make(UserQueueRepository::class);

        $this->queueController = new QueueController(
            $this->queueRepository,
            $this->userQueueRepository
        );
    }

    public function test_shop_can_edit_queue_type_successfully()
    {
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
            'email_verified_at' => now(),
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

        // Authenticate the user
        $this->actingAs($user);

        // Create an existing queue type
        $queueType = Queue::factory()->create([
            'name' => 'Table TEST',
            'description' => 'โต๊ะ TEST',
            'is_available' => true,
            'tag' => "TS",
            'shop_id' => $shop->id,
        ]);

        // Prepare updated data
        $updatedData = [
            'name' => 'Updated Queue Type',
            'description' => 'Updated Description',
        ];

        // Create request
        $request = Request::create(
            "/api/queues/{$queueType->id}",
            'PATCH',
            $updatedData
        );

        // Call update method
        $response = $this->queueController->update($request, $queueType);

        // Assert successful update
        $this->assertEquals(200, $response->getStatusCode());

        // Refresh the model and verify updates
        $queueType->refresh();
        $this->assertEquals($updatedData['name'], $queueType->name);
        $this->assertEquals($updatedData['description'], $queueType->description);
    }
    /**
     * Test editing queue type with invalid data
     */
    public function test_shop_cannot_edit_queue_type_with_invalid_data()
    {
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
            'email_verified_at' => now(),
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

        // Authenticate the user
        $this->actingAs($user);

        // Create an existing queue type
        $queueType = Queue::factory()->create([
            'name' => 'Table TEST',
            'description' => 'โต๊ะ TEST',
            'is_available' => true,
            'tag' => "TS",
            'shop_id' => $shop->id,
        ]);

        // Prepare updated data
        $updatedData = [
            'name' => '1',
            'description' => '08347123',
            'is_available' => 'true',
        ];

        // Create request
        $request = Request::create(
            "/api/queues/{$queueType->id}",
            'PATCH',
            $updatedData
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->queueController->update($request, $queueType);
    }

    /**
     * Test that a shop can only edit its own queue type
     */
    public function test_shop_cannot_edit_another_shops_queue_type()
    {
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'name' => "test02",
            'email' => "test2@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
            'email_verified_at' => now(),
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

        // Authenticate the user
        $this->actingAs($user2);

        // Create an existing queue type
        $queueType = Queue::factory()->create([
            'name' => 'Table TEST',
            'description' => 'โต๊ะ TEST',
            'is_available' => true,
            'tag' => "TS",
            'shop_id' => $shop->id,
        ]);

        // Prepare updated data
        $updatedData = [
            'name' => 'Updated Queue Type',
            'description' => 'Updated Description',
        ];

        // Create request
        $request = Request::create(
            "/api/queues/{$queueType->id}",
            'PATCH',
            $updatedData
        );

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->queueController->update($request, $queueType);
    }

    public function test_shop_can_create_queue_successfully(){
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP', 'email_verified_at' => now(),
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

        // Authenticate the user
        $this->actingAs($user);

        // Prepare updated data
        $createData = [
            'name' => 'Table TEST',
            'description' => 'โต๊ะ TEST',
            'is_available' => true,
            'tag' => "TS",
            'shop_id' => $shop->id,
        ];

        // Create request
        $request = Request::create(
            "/api/queues",
            'POST',
            $createData
        );

        // Call update method
        $response = $this->queueController->store($request);

        // Assert successful update
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_shop_cannot_create_queue_with_invalid_data(){
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
            'email_verified_at' => now(),
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

        // Authenticate the user
        $this->actingAs($user);

        // Prepare updated data
        $createData = [
            'name' => 1,
            'description' => 1,
            'is_available' => 'true',
            'tag' => 2,
            'shop_id' => $shop->id,
        ];

        // Create request
        $request = Request::create(
            "/api/queues",
            'POST',
            $createData
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $response = $this->queueController->store($request);
    }


    public function test_shop_cannot_create_queue_with_already_exist(){
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
            'email_verified_at' => now(),
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

        // Authenticate the user
        $this->actingAs($user);

        $queue1 = Queue::factory()->create([
            'name' => 'Table TEST',
            'description' => 'โต๊ะ TEST',
            'is_available' => true,
            'tag' => "TS",
            'shop_id' => $shop->id,
        ]);

        // Prepare updated data
        $createData = [
            'name' => 'Table TEST',
            'description' => 'โต๊ะ TEST',
            'is_available' => true,
            'tag' => "TS",
            'shop_id' => $shop->id,
        ];

        // Create request
        $request = Request::create(
            "/api/queues",
            'POST',
            $createData
        );

        $response = $this->queueController->store($request);
        $this->assertEquals("This name is already in use" , $response->getData()->message);
    }

    public function test_shop_can_show_all_queue_correct()
    {
        // สร้าง User ที่มี role เป็น SHOP
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
            'email_verified_at' => now(),
        ]);

        // ใช้ Faker สร้างข้อมูลร้านค้า
        $faker = FakerFactory::create('th_TH');
        $shop = Shop::factory()->create([
            'user_id' => $user->id,
            'name' => $faker->company,
            'image_url' => env('APP_URL') . '/api/images/shops+defaults+images+avatar.png',
            'phone' => $faker->phoneNumber,
            'address' => $faker->address,
            'description' => "Open leaw",
            'is_open' => $faker->boolean,
            'latitude' => $faker->latitude(13.826, 13.846), // Adjusted range for Ratchayothin - Kasetsart University
            'longitude' => $faker->longitude(100.566, 100.592), // Adjusted range for Ratchayothin - Kasetsart University
        ]);

        // สร้าง Queue ที่เกี่ยวข้องกับร้าน
        $queue1 = Queue::create([
            'name' => 'Table 1',
            'description' => 'โต๊ะ TEST',
            'is_available' => true,
            'tag' => "TS",
            'shop_id' => $shop->id,
        ]);

        $queue2 = Queue::create([
            'name' => 'Table 2',
            'description' => 'โต๊ะ TEST',
            'is_available' => true,
            'tag' => "TS",
            'shop_id' => $shop->id,
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // เรียก API
        $response = $this->getJson("/api/queues?shop_id={$shop->id}");

        // ตรวจสอบ Response Status
        $response->assertStatus(200);

        // ตรวจสอบข้อมูล JSON ที่ส่งกลับมา
        $response->assertJson([
            "data" => [
                [
                    "id" => $queue1->id,
                    "name" => $queue1->name,
                    "description" => $queue1->description,
                    "is_available" => $queue1->is_available,
                    "tag" => $queue1->tag,
                    "shop_id" => $queue1->shop_id,
                ],
                [
                    "id" => $queue2->id,
                    "name" => $queue2->name,
                    "description" => $queue2->description,
                    "is_available" => $queue2->is_available,
                    "tag" => $queue2->tag,
                    "shop_id" => $queue2->shop_id,
                ],
            ],
        ]);
    }

    public function test_shop_show_appropriate_message_when_queue_not_exist()
    {
        // สร้าง User ที่มี role เป็น SHOP
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
            'email_verified_at' => now(),
        ]);

        // ใช้ Faker สร้างข้อมูลร้านค้า
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

        // Authenticate the user
        $this->actingAs($user);

        // เรียก API
        $response = $this->getJson("/api/queues?shop_id={$shop->id}");

        // ตรวจสอบ Response Status
        $response->assertStatus(200);

        // ตรวจสอบข้อมูล JSON ที่ส่งกลับมา
        $response->assertJson([
            "data" => [],
            "message"=>  "No queues found",
        ]);
    }

    public function test_shop_show_latest_queue()
    {
        // สร้าง User ที่มี role เป็น SHOP
        $user = User::factory()->create([
            'name' => "test01",
            'email' => "test@test.com",
            'password' => Hash::make('password'),
            'phone' => "0000000000",
            'role' => 'SHOP',
            'email_verified_at' => now(),
        ]);

        // ใช้ Faker สร้างข้อมูลร้านค้า
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

        // Authenticate the user
        $this->actingAs($user);

        $queue = Queue::factory()->create([
            'name' => 'Table TEST',
            'description' => 'โต๊ะ TEST',
            'is_available' => true,
            'tag' => "TS",
            'shop_id' => $shop->id,
        ]);

        // Prepare updated data
        $updatedData = [
            'name' => 'Updated Queue Type',
            'description' => 'Updated Description',
        ];

        // Create request
        $request = Request::create(
            "/api/queues/{$queue->id}",
            'PATCH',
            $updatedData
        );

        // Call update method
        $this->queueController->update($request, $queue);

        // เรียก API
        $response = $this->getJson("/api/queues?shop_id={$shop->id}");

        // ตรวจสอบ Response Status
        $response->assertStatus(200);

        // ตรวจสอบข้อมูล JSON ที่ส่งกลับมา
        $response->assertJson([
            "data" => [
                [
                    "id" => $queue->id,
                    "name" => $updatedData["name"],
                    "description" => $updatedData["description"],
                    "is_available" => $queue->is_available,
                    "tag" => $queue->tag,
                    "shop_id" => $queue->shop_id,
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

}
