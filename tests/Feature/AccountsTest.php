<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountsTest extends TestCase
{
    use RefreshDatabase;

    protected $passwordResetToken;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => "user1",
            'email' => "usertest1@gmail.com",
            'password' => Hash::make('password'),
            'phone' => "0999999999",
            'role' => 'CUSTOMER',
            'email_verified_at' => now(),
        ]);
    }

    public function test_user_can_send_forgot_password_request()
    {
        $response = $this->postJson('/api/auth/forget-password', ['email' => $this->user->email]);

        $response->assertStatus(200);

        $passwordResetEntry = DB::table('password_resets')->where('email', $this->user->email)->first();

        $this->assertNotNull($passwordResetEntry);
        $this->assertNotNull($passwordResetEntry->token);
        $this->passwordResetToken = $passwordResetEntry->token;
    }
//
    public function test_invalid_token_request()
    {
        $newPassword = 'NewPassword123-';

        $resetPasswordData = [
            'token' => 'WrongTokenForSure',
            'email' => $this->user->email,
            'password' => $newPassword
        ];

        $response = $this->postJson('/api/auth/reset-password', $resetPasswordData);
        $response->assertStatus(400);
    }
//
    public function test_user_can_change_password()
    {
        if (!isset($this->user)) {
            $this->user = User::factory()->create();
        }

        $plainToken = Str::random(32);
        $hashedToken = Hash::make($plainToken);

        DB::table('password_resets')->where('email', $this->user->email)->delete();

        DB::table('password_resets')->insert([
            'email' => $this->user->email,
            'token' => $hashedToken,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(30)
        ]);

        $newPassword = 'NewPassword123-';

        $resetPasswordData = [
            'token' => $plainToken,
            'email' => $this->user->email,
            'password' => $newPassword
        ];

        $response = $this->postJson('/api/auth/reset-password', $resetPasswordData);
        $response->assertStatus(200);

        $loginData = [
            'email' => $this->user->email,
            'password' => $newPassword
        ];

        $response = $this->postJson('/api/auth/login', $loginData);
        $response->assertStatus(201);

        $this->assertNull(DB::table('password_resets')->where('email', $this->user->email)->first());
    }
//
//    public function test_authorized_user_can_update_profile()
//    {
//        $loginResponse = $this->postJson('/api/auth/login', [
//            'email' => $this->user->email,
//            'password' => 'password'
//        ]);
//
//        $loginResponse->assertStatus(201);
//        $token = $loginResponse->json('data.token');
//
//        // Decrypt token if needed
//        $decryptResponse = $this->postJson('/api/auth/decrypt', [
//            'encrypted' => $token
//        ]);
//
//        $decryptResponse->assertStatus(201);
//        $decryptedToken = $decryptResponse->json('data.plain_text');
//
//        $response = $this->withHeaders([
//            'Authorization' => 'Bearer ' . $decryptedToken,
//        ])->putJson("/api/users/{$this->user->id}", [
//            'name' => 'Updated Name',
//            'phone' => '9876543210'
//        ]);
//
//        $response->assertStatus(200);
//
//        $this->assertDatabaseHas('users', [
//            'id' => $this->user->id,
//            'name' => 'Updated Name',
//            'phone' => '9876543210'
//        ]);
//    }
//
//    public function test_unauthorized_user_cannot_update_another_users_profile()
//    {
//        // Create another user
//        $anotherUser = User::factory()->create([
//            'name' => "user2",
//            'email' => "usertest2@gmail.com",
//            'password' => Hash::make('password'),
//            'phone' => "0888888888",
//            'role' => 'CUSTOMER',
//            'email_verified_at' => now(),
//        ]);
//
//        // Login as the main user
//        $loginResponse = $this->postJson('/api/auth/login', [
//            'email' => $this->user->email,
//            'password' => 'password'
//        ]);
//
//        $loginResponse->assertStatus(201);
//        $token = $loginResponse->json('data.token');
//
//        // Decrypt token
//        $decryptResponse = $this->postJson('/api/auth/decrypt', [
//            'encrypted' => $token
//        ]);
//
//        $decryptResponse->assertStatus(201);
//        $decryptedToken = $decryptResponse->json('data.plain_text');
//
//        // Try to update another user's profile
//        $response = $this->withHeaders([
//            'Authorization' => 'Bearer ' . $decryptedToken,
//        ])->putJson("/api/users/{$anotherUser->id}", [
//            'name' => 'Hacked Name',
//            'phone' => '1112223333'
//        ]);
//
//        // Should get 403 Forbidden
//        $response->assertStatus(403);
//
//        // Verify the other user's data was not changed
//        $this->assertDatabaseMissing('users', [
//            'id' => $anotherUser->id,
//            'name' => 'Hacked Name',
//            'phone' => '1112223333'
//        ]);
//    }
//
//    public function test_authenticated_user_can_view_profile()
//    {
//        $loginResponse = $this->postJson('/api/auth/login', [
//            'email' => $this->user->email,
//            'password' => 'password'
//        ]);
//
//        $loginResponse->assertStatus(201);
//        $token = $loginResponse->json('data.token');
//
//        $decryptResponse = $this->postJson('/api/auth/decrypt', [
//            'encrypted' => $token
//        ]);
//
//        $decryptResponse->assertStatus(201);
//        $decryptedToken = $decryptResponse->json('data.plain_text');
//
//        $response = $this->withHeaders([
//            'Authorization' => 'Bearer ' . $decryptedToken,
//        ])->getJson("/api/users/{$this->user->id}");
//
//        $response->assertStatus(200);
//    }
//
//    public function test_authorized_user_can_update_avatar()
//    {
//        $loginResponse = $this->postJson('/api/auth/login', [
//            'email' => $this->user->email,
//            'password' => 'password'
//        ]);
//
//        $loginResponse->assertStatus(201);
//        $token = $loginResponse->json('data.token');
//
//        // Decrypt token
//        $decryptResponse = $this->postJson('/api/auth/decrypt', [
//            'encrypted' => $token
//        ]);
//
//        $decryptResponse->assertStatus(201);
//        $decryptedToken = $decryptResponse->json('data.plain_text');
//
//        $image = UploadedFile::fake()->image('avatar.jpg');
//
//        $response = $this->withHeaders([
//            'Authorization' => 'Bearer ' . $decryptedToken,
//        ])->postJson("/api/users/{$this->user->id}/avatar", [
//            'image' => $image
//        ]);
//
//        $response->assertStatus(200)
//            ->assertJsonStructure(['data' => ['url']]);
//
//        $avatarUrl = $response->json('data.url');
//
//        $this->assertStringContainsString("customers/{$this->user->id}/images/avatars/", str_replace('+', '/', parse_url($avatarUrl, PHP_URL_PATH)));
//        $this->user->refresh();
//        $this->assertEquals($avatarUrl, $this->user->image_url);
//    }
//
//    public function test_unauthorized_user_cannot_update_another_users_avatar()
//    {
//        $anotherUser = User::factory()->create([
//            'name' => "user2",
//            'email' => "usertest2@gmail.com",
//            'password' => Hash::make('password'),
//            'phone' => "0888888888",
//            'role' => 'CUSTOMER',
//            'email_verified_at' => now(),
//        ]);
//
//        $loginResponse = $this->postJson('/api/auth/login', [
//            'email' => $this->user->email,
//            'password' => 'password'
//        ]);
//
//        $loginResponse->assertStatus(201);
//        $token = $loginResponse->json('data.token');
//
//        $decryptResponse = $this->postJson('/api/auth/decrypt', [
//            'encrypted' => $token
//        ]);
//
//        $decryptResponse->assertStatus(201);
//        $decryptedToken = $decryptResponse->json('data.plain_text');
//
//        $image = UploadedFile::fake()->image('avatar.jpg');
//
//        $response = $this->withHeaders([
//            'Authorization' => 'Bearer ' . $decryptedToken,
//        ])->postJson("/api/users/{$anotherUser->id}/avatar", [
//            'image' => $image
//        ]);
//
//        $response->assertStatus(403);
//
//        $anotherUser->refresh();
//    }
//
//    public function test_can_retrieve_user_avatar_image()
//    {
//        $loginResponse = $this->postJson('/api/auth/login', [
//            'email' => $this->user->email,
//            'password' => 'password'
//        ]);
//
//        $loginResponse->assertStatus(201);
//        $token = $loginResponse->json('data.token');
//
//        $decryptResponse = $this->postJson('/api/auth/decrypt', [
//            'encrypted' => $token
//        ]);
//
//        $decryptResponse->assertStatus(201);
//        $decryptedToken = $decryptResponse->json('data.plain_text');
//
//        $image = UploadedFile::fake()->image('avatar.jpg');
//
//        $uploadResponse = $this->withHeaders([
//            'Authorization' => 'Bearer ' . $decryptedToken,
//        ])->postJson("/api/users/{$this->user->id}/avatar", [
//            'image' => $image
//        ]);
//
//        $avatarUrl = $uploadResponse->json('data.url');
//
//        $imagePathWithPlus = str_replace(env("APP_URL") . '/api/images/', '', $avatarUrl);
//
//        $response = $this->get("/api/images/{$imagePathWithPlus}");
//
//        $response->assertStatus(200)
//            ->assertHeader('Content-Type', 'image/png');
//    }
}
