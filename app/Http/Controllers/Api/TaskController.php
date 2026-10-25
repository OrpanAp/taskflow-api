<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Task::class);

        $validated = $request->validate([
            'status' => [
                'sometimes',
                'required',
                'in:pending,in_progress,completed',
            ],
            'sort' => [
                'sometimes',
                'required',
                'in:latest,oldest',
            ],
            'search' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'per_page' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $query = $request->user()->tasks();

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['search'])) {
            $query->where(
                'title',
                'like',
                '%' . $validated['search'] . '%'
            );
        }

        $sort = $validated['sort'] ?? 'latest';

        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc');
        } else {
            $query->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc');
        }

        $perPage = $validated['per_page'] ?? 10;
        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => TaskResource::collection($paginator)->resolve($request),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Task::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:pending,in_progress,completed'],
        ]);

        $task = $request->user()
            ->tasks()
            ->create($validated);

        return response()->json([
            'task' => $task,
        ], 201);
    }


    public function update(Request $request, int $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:pending,in_progress,completed'],
        ]);

        $task = $request->user()
            ->tasks()
            ->findOrFail($task);

        Gate::authorize('update', $task);

        $task->update($validated);

        return response()->json([
            'task' => new TaskResource($task->fresh()),
        ]);
    }

    public function destroy(Request $request, int $task): JsonResponse
    {
        $task = $request->user()
            ->tasks()
            ->findOrFail($task);

        Gate::authorize('delete', $task);

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }
}
