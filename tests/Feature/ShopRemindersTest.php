<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Models\User;
use App\Repositories\ReminderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;

class ShopRemindersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $reminderRepository;
    private $shopUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a shop user
        $this->shopUser = User::factory()->state([
            'role' => 'SHOP'
        ])->create();

        $this->reminderRepository = Mockery::mock(ReminderRepository::class);
        $this->app->instance(ReminderRepository::class, $this->reminderRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test when shop's reminders exist, system displays all pending reminders
     */
    public function test_when_shop_reminders_exist_system_displays_pending_reminders()
    {
        // Arrange
        $shopId = $this->shopUser->id;
        $remindersList = [
            [
                'id' => 1,
                'shop_id' => $shopId,
                'title' => 'First Reminder',
                'description' => 'Description for first reminder',
                'due_date' => now()->addDays(2)->toISOString(),
                'status' => 'pending'
            ],
            [
                'id' => 2,
                'shop_id' => $shopId,
                'title' => 'Second Reminder',
                'description' => 'Description for second reminder',
                'due_date' => now()->addDays(5)->toISOString(),
                'status' => 'pending'
            ]
        ];


        // Mock the repository method
        $this->reminderRepository
            ->shouldReceive('getAllRemindersByShopId')
            ->with($shopId)
            ->once()
            ->andReturn(collect($remindersList));

        // Act - Use actingAs to simulate authenticated user
        $response = $this->actingAs($this->shopUser)
            ->getJson("/api/shops/reminders/{$shopId}");

        // Assert
        $response->assertStatus(200)
            ->assertJson($remindersList)
            ->assertJsonCount(2);
    }

    /**
     * Test when no shop's reminders exist, system displays message
     */
    public function test_when_no_shop_reminders_exist_system_displays_no_reminders_message()
    {
        // Arrange
        $shopId = $this->shopUser->id;

        // Mock the repository method
        $this->reminderRepository
            ->shouldReceive('getAllRemindersByShopId')
            ->with($shopId)
            ->once()
            ->andReturn(collect([]));

        // Act - Use actingAs to simulate authenticated user
        $response = $this->actingAs($this->shopUser)
            ->getJson("/api/shops/reminders/{$shopId}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([])
            ->assertJsonCount(0);
    }

    /**
     * Test when merchant creates a reminder, system displays the latest reminder
     */
    public function test_when_merchant_creates_reminder_system_displays_latest_reminder()
    {
        // Arrange
        $shopId = $this->shopUser->id;
        $reminderData = [
            'shop_id' => $shopId,
            'title' => 'New Reminder',
            'description' => 'Description for new reminder',
            'due_date' => now()->addDays(7)->format('Y-m-d H:i:s')
        ];

        $createdReminder = array_merge($reminderData, [
            'id' => 3,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s')
        ]);

        // Mock the repository method
        $this->reminderRepository
            ->shouldReceive('create')
            ->with(Mockery::on(function($arg) use ($reminderData) {
                return $arg['shop_id'] == $reminderData['shop_id'] &&
                    $arg['title'] == $reminderData['title'] &&
                    $arg['description'] == $reminderData['description'] &&
                    isset($arg['due_date']);
            }))
            ->once()
            ->andReturn($createdReminder);

        // Act - Use actingAs to simulate authenticated user
        $response = $this->actingAs($this->shopUser)
            ->postJson('/api/shops/reminders', $reminderData);

        // Assert
        $response->assertStatus(200)
            ->assertJson($createdReminder);
    }

    /**
     * Test validation for creating a reminder
     */
    public function test_reminder_creation_validates_required_fields()
    {
        // Arrange
        $invalidData = [
            'shop_id' => '',
            'title' => '',
            'description' => '',
            'due_date' => 'not-a-date'
        ];

        // Act - Use actingAs to simulate authenticated user
        $response = $this->actingAs($this->shopUser)
            ->postJson('/api/shops/reminders', $invalidData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shop_id', 'title', 'description', 'due_date']);
    }

    /**
     * Test marking a reminder as done
     */
    public function test_marking_reminder_as_done()
    {
        // Arrange
        $shopId = $this->shopUser->id;
        $reminderId = 1;
        $updatedReminder = [
            'id' => $reminderId,
            'shop_id' => $shopId,
            'status' => 'completed'
        ];

        // Mock the repository method
        $this->reminderRepository
            ->shouldReceive('markAsDone')
            ->with($reminderId)
            ->once()
            ->andReturn($updatedReminder);

        // Act - Use actingAs to simulate authenticated user
        $response = $this->actingAs($this->shopUser)
            ->patchJson("/api/shops/reminders/{$reminderId}");

        // Assert
        $response->assertStatus(200)
            ->assertJson(['message' => 'Reminder marked as completed']);
    }

    /**
     * Test invalid shop_id when fetching reminders
     */
    public function test_fetch_reminders_with_invalid_shop_id()
    {
        // Arrange
        $invalidShopId = 'invalid-id';

        // Act
        $response = $this->actingAs($this->shopUser)
            ->getJson("/api/shops/reminders/{$invalidShopId}");

        // Assert
        $response->assertStatus(400)
            ->assertJson(['error' => 'shop_id is required']);
    }

    /**
     * Test invalid reminder_id when marking as done
     */

    public function test_marking_reminder_as_done_with_invalid_id()
    {
        // Arrange
        $invalidReminderId = 'invalid-id';

        // Act
        $response = $this->actingAs($this->shopUser)
            ->patchJson("/api/shops/reminders/{$invalidReminderId}");

        // Assert
        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid reminder ID']);
    }

    /**
     * Test invalid reminder's data when creating reminder
     */

    public function test_reminder_creation_fails_with_invalid_data()
    {
        // Arrange
        $invalidDataList = [
            [
                'shop_id' => 'not-a-number',
                'title' => 'Valid Title',
                'description' => 'Valid Description',
                'due_date' => now()->addDays(3)->format('Y-m-d H:i:s')
            ],
            [
                'shop_id' => 1,
                'title' => 'Valid Title',
                'description' => 'Valid Description',
                'due_date' => 'invalid-date-format'
            ],
            [
                'shop_id' => 1,
                'title' => '',
                'description' => '',
                'due_date' => now()->addDays(3)->format('Y-m-d H:i:s')
            ]
        ];

        foreach ($invalidDataList as $invalidData) {
            // Act
            $response = $this->actingAs($this->shopUser)
                ->postJson('/api/shops/reminders', $invalidData);

            // Assert
            $response->assertStatus(422);
        }
    }


    public function test_fetch_reminders_fails_with_non_numeric_shop_id()
    {
        // Arrange
        $invalidShopId = 'abc123';

        // Act
        $response = $this->actingAs($this->shopUser)
            ->getJson("/api/shops/reminders/{$invalidShopId}");

        // Assert
        $response->assertStatus(400)
            ->assertJson(['error' => 'shop_id is required']);
    }


}

