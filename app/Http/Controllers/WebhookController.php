<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebhookRequest;
use App\Http\Resources\WebhookResource;
use App\Models\Project;
use App\Models\Webhook;

class WebhookController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('update', $project);

        return WebhookResource::collection(
            $project->webhooks()->paginate(10)
        );
    }

    public function store(StoreWebhookRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $webhook = $project->webhooks()->create(
            $request->validated()
        );

        return (new WebhookResource($webhook))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Project $project, Webhook $webhook)
    {
        abort_unless(
            $webhook->project_id === $project->id,
            404
        );

        $this->authorize('delete', $webhook);

        $webhook->delete();

        return response()->noContent();
    }
}
