<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase; // Resets database after each test

    /**
     * A basic feature test example.
     *
     * @return void
     */

        /** @test */
    public function a_user_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'fullname@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'fullname@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'user' => ['id', 'name', 'email', 'created_at', 'updated_at']
                 ]);
    }

      /** @test */
      public function a_user_cannot_login_with_wrong_credentials()
      {
          User::factory()->create([
              'email' => 'fullname@example.com',
              'password' => bcrypt('password123')
          ]);
  
          $response = $this->postJson('/api/login', [
              'email' => 'fullname@example.com',
              'password' => 'wrongpassword'
          ]);
  
          $response->assertStatus(401)
                   ->assertJson(['message' => 'Invalid email or password']);
      }
  
}
