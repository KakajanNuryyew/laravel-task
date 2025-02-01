<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Policies\TaskPolicy;

class TaskPolicyTest extends TestCase
{
    protected $admin;
    protected $user;
    protected $task;
    protected $taskPolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = new User();
        $this->admin->role = 'admin';
        $this->admin->id = 1;

        $this->user = new User();
        $this->user->role = 'user';
        $this->user->id = 2;

        $this->task = new Task();
        $this->task->user_id = 2; 

        $this->taskPolicy = new TaskPolicy();
    }

    /** @test */
    public function user_can_view_own_task()
    {
        $this->assertTrue($this->taskPolicy->view($this->user, $this->task));
    }

    /** @test */
    public function admin_can_view_any_task()
    {
        $this->assertTrue($this->taskPolicy->view($this->admin, $this->task));
    }

    /** @test */
    public function user_cannot_view_other_users_task()
    {
        $otherUser = $this->createMock(User::class);
        $otherUser->id = 3; 

        $this->assertFalse($this->taskPolicy->view($otherUser, $this->task));
    }

    /** @test */
    public function user_can_create_task()
    {
        $this->assertTrue($this->taskPolicy->create($this->user));
    }

    /** @test */
    public function user_can_update_own_task()
    {
        $this->assertTrue($this->taskPolicy->update($this->user, $this->task));
    }

    /** @test */
    public function admin_can_update_any_task()
    {
        $this->assertTrue($this->taskPolicy->update($this->admin, $this->task));
    }

    /** @test */
    public function user_cannot_update_other_users_task()
    {
        $otherUser = $this->createMock(User::class);
        $otherUser->id = 3; 

        $this->assertFalse($this->taskPolicy->update($otherUser, $this->task));
    }

    /** @test */
    public function user_can_delete_own_task()
    {
        $this->assertTrue($this->taskPolicy->delete($this->user, $this->task));
    }

    /** @test */
    public function admin_can_delete_any_task()
    {
        $this->assertTrue($this->taskPolicy->delete($this->admin, $this->task));
    }

    /** @test */
    public function user_cannot_delete_other_users_task()
    {
        $otherUser = $this->createMock(User::class);
        $otherUser->id = 3; 

        $this->assertFalse($this->taskPolicy->delete($otherUser, $this->task));
    }
}
