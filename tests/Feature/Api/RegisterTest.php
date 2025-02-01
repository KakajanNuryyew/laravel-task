<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    
    /** @test */
    public function a_user_can_register_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Full name',
            'email' => 'fullname@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(201) 
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email', 'created_at', 'updated_at']
                ]);
        
        $this->assertDatabaseHas('users', ['email' => 'fullname@example.com']);
    }

    /** @test */
    public function a_user_requires_name_email_and_password()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])->assertJsonStructure([
                'error' => ['name', 'email', 'password'],
            ]);
    }

    /** @test */
    public function a_user_requires_a_unique_email()
    {
        User::factory()->create(['email' => 'fullname@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Full name',
            'email' => 'fullname@example.com',
            'password' => 'password123',
        ]);

    $response->assertStatus(422)->assertJson([
        'success' => false,
        'message' => 'Validation failed',
        ])->assertJsonStructure([
            'error' => ['email'],
        ]);
    }

       /** @test */
    public function a_user_requires_a_minimum_password_length()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Full name',
            'email' => 'fullname@example.com',
            'password' => '123',
        ]);

        $response->assertStatus(422)->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])->assertJsonStructure([
                'error' => ['password'],
            ]);
    }
}
