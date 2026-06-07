<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;


class ProjectController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        $projects = Project::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->paginate(10);

        return ProjectResource::collection($projects);
    }

    public function create()
    {
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            'name' => $request->name,
            'owner_id' => auth()->id(),
        ]);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource($project);
    }

    public function edit(Project $project)
    {
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);
        $project->update($request->validated());
        return new ProjectResource($project);
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->noContent();
    }
}
