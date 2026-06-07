<?php

namespace App\Http\Controllers;

use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{

    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->priority, function ($query, $priority) {
                $query->where('priority', $priority);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereRaw(
                    'LOWER(title) LIKE ?',
                    ['%' . strtolower($search) . '%']
                );
            })
            ->when($request->due_date_from, function ($query, $date) {
                $query->whereDate('due_date', '>=', $date);
            })
            ->when($request->due_date_to, function ($query, $date) {
                $query->whereDate('due_date', '<=', $date);
            })
            ->orderByDesc('id')
            ->cursorPaginate(10);

        return TaskResource::collection($tasks);
    }

    public function create()
    {
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $task = $project->tasks()->create(
            $request->validated()
        );

        event(new TaskCreated($task));

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project, Task $task)
    {
        abort_unless($task->project_id === $project->id, 404);

        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function edit(Task $task)
    {
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        abort_unless($task->project_id === $project->id, 404);

        $this->authorize('update', $task);

        $oldStatus = $task->status;

        $task->update($request->validated());

        if ($oldStatus !== $task->status) {
            event(new TaskStatusChanged(
                $task,
                $oldStatus,
                $task->status
            ));
            if ($task->status === 'done') {
                event(new TaskCompleted($task));
            }
        }
        return new TaskResource($task);
    }

    public function destroy(Project $project, Task $task)
    {
        abort_unless($task->project_id === $project->id, 404);

        $this->authorize('delete', $task);
        $task->delete();
        return response()->noContent();
    }
}
