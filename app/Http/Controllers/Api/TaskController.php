<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Task::class, 'task'); // Automatically authorizes actions
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $role = $request->user()->role;

        $tasksQuery = Task::query();

        if ($role == 'user') {
            $tasksQuery = $tasksQuery->where('user_id', $request->user()->id);
        }

        if($request->status) {
            $tasksQuery = $tasksQuery->where('status', $request->status);
        }

        if($request->from_date && $request->to_date) {
            $tasksQuery = $tasksQuery->whereBetween('created_at', [$request->from_date . ' 00:00:00', $request->to_date . ' 23:59:59']);
        }

        if($request->order_by) {
            $tasksQuery = $tasksQuery->orderBy($request->order_by, 'ASC');
        }

        $cacheKey = Task::CACHE_KEY.'.' . md5(json_encode($request->query()));

        $tasks = Cache::remember($cacheKey, Task::CACHE_MINUTES, function () use ($tasksQuery) {
            return $tasksQuery->get();
        });
                
        return response()->json([
            'success' => true,
            'message' => '',
            'tasks' => $tasks,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Cache::forget(Task::CACHE_KEY.'.*');

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $validator->errors()
            ], 422);
        }

        $task = Task::create([
            'user_id' => $request->user()->id, // Get authenticated user ID
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully!',
            'task' => $task
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $this->authorize('update', $task);

        Cache::forget(Task::CACHE_KEY.'.*');

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', [Task::STATUS_PENDING, Task::STATUS_COMPLETED]),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $validator->errors()
            ], 422);
        }

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or not authorized'
            ], 404);
        }

        $task->status = $request->status;
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully!',
            'task' => $task
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $this->authorize('delete', $task);

        Cache::forget(Task::CACHE_KEY.'.*');
        
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or not authorized'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully!'
        ], 200);
    }
}
