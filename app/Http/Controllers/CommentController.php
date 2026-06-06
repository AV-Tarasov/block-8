<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        return CommentResource::collection(
            $task->comments()
                ->with('user')
                ->latest()
                ->paginate(10)
        );
    }
    public function store(StoreCommentRequest $request, Task $task): CommentResource
    {
        $this->authorize('view', $task);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->validated()['body'],
        ]);
        return new CommentResource($comment);
    }
    public function show(Task $task, Comment $comment)
    {
        abort_unless($comment->task_id === $task->id, 404);

        $this->authorize('view', $comment);

        return new CommentResource($comment->load('user'));
    }
    public function update(StoreCommentRequest $request, Task $task, Comment $comment): CommentResource
    {
        abort_unless($comment->task_id === $task->id, 404);

        $this->authorize('update', $comment);

        $comment->update($request->validated());

        return new CommentResource($comment);
    }
    public function destroy(Task $task, Comment $comment)
    {
        abort_unless($comment->task_id === $task->id, 404);
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->noContent();
    }
}
