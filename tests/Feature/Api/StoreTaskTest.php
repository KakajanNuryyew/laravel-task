<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class StoreTaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */

    /** @test */
    public function an_authenticated_user_can_create_a_task()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $taskData = [
            'title' => 'Test Task',
            'description' => 'This is a test task description.',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Task created successfully!',
                'task' => [
                    'title' => 'Test Task',
                    'description' => 'This is a test task description.',
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task description.',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_requires_a_title_to_create_a_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/tasks', [
            'description' => 'Missing title'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])->assertJsonStructure([
                'error' => ['title'],
            ]);
    }

    /** @test */
    public function an_unauthenticated_user_cannot_create_a_task()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Unauthorized Task',
            'description' => 'This should not be created.',
        ]);

        $response->assertStatus(401); // Unauthorized
    }

}
