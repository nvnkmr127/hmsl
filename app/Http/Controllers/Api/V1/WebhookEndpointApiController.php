<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WebhookEndpointResource;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WebhookEndpointApiController extends Controller
{
    /**
     * Display a listing of webhook endpoints.
     */
    public function index(Request $request)
    {
        $endpoints = $request->user()->webhookEndpoints ?? WebhookEndpoint::where('created_by', $request->user()->id)->get();
        return WebhookEndpointResource::collection($endpoints);
    }

    /**
     * Store a new webhook endpoint.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'events' => 'required|array',
            'events.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $endpoint = WebhookEndpoint::create([
            'name' => $request->name,
            'url' => $request->url,
            'events' => $request->events,
            'secret' => 'whsec_' . Str::random(32),
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return (new WebhookEndpointResource($endpoint))
            ->additional(['meta' => ['secret' => $endpoint->secret]]);
    }

    /**
     * Display the specified webhook endpoint.
     */
    public function show(WebhookEndpoint $webhook_endpoint)
    {
        $this->authorizeOwner($webhook_endpoint);
        return new WebhookEndpointResource($webhook_endpoint);
    }

    /**
     * Update the specified webhook endpoint.
     */
    public function update(Request $request, WebhookEndpoint $webhook_endpoint)
    {
        $this->authorizeOwner($webhook_endpoint);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|url',
            'events' => 'sometimes|required|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $webhook_endpoint->update($request->all());

        return new WebhookEndpointResource($webhook_endpoint);
    }

    /**
     * Remove the specified webhook endpoint.
     */
    public function destroy(WebhookEndpoint $webhook_endpoint)
    {
        $this->authorizeOwner($webhook_endpoint);
        $webhook_endpoint->delete();
        return response()->json(['message' => 'Webhook endpoint deleted successfully.']);
    }

    protected function authorizeOwner(WebhookEndpoint $endpoint)
    {
        if ($endpoint->created_by !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
