<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'search' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'sort' => [
                'sometimes',
                'required',
                'in:latest,oldest',
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
            $query->where('title', 'like', '%' . $validated['search'] . '%');
        }

        $sort = $validated['sort'] ?? 'latest';

        $query->orderBy(
            'created_at',
            $sort === 'oldest' ? 'asc' : 'desc'
        )->orderBy(
            'id',
            $sort === 'oldest' ? 'asc' : 'desc'
        );

        $paginator = $query->paginate($validated['per_page'] ?? 10);

        return response()->json([
            'data' => TaskResource::collection($paginator)->resolve($request),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Task::class);

        $tasks = $request->user()->tasks();

        $counts = (clone $tasks)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return response()->json([
            'all' => (int) $counts->sum(),
            'pending' => (int) $counts->get('pending', 0),
            'in_progress' => (int) $counts->get('in_progress', 0),
            'completed' => (int) $counts->get('completed', 0),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Task::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => [
                'sometimes',
                'required',
                'in:pending,in_progress,completed',
            ],
        ]);

        $task = $request->user()->tasks()->create($validated);

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => (new TaskResource($task))->resolve($request),
        ], 201);
    }
}
