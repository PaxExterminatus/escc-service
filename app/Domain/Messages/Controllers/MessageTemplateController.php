<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Models\MessageTemplate;
use App\Domain\Messages\Requests\RenderMessageTemplateRequest;
use App\Domain\Messages\Requests\StoreMessageTemplateRequest;
use App\Domain\Messages\Requests\UpdateMessageTemplateRequest;
use App\Domain\Messages\Resources\MessageTemplateResource;
use App\Domain\Messages\Services\TemplateParamsResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Message Templates (SMS/Email)
 */
class MessageTemplateController extends Controller
{
    /**
     * Templates: list
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $templates = MessageTemplate::when($request->boolean('active_only'), fn ($query) => $query->where('is_active', 1))
            ->orderBy('name')
            ->get();

        return MessageTemplateResource::collection($templates);
    }

    /**
     * Template: create
     */
    public function store(StoreMessageTemplateRequest $request): MessageTemplateResource
    {
        return MessageTemplateResource::make(MessageTemplate::create($request->validated()));
    }

    /**
     * Template: update
     */
    public function update(UpdateMessageTemplateRequest $request, int $id): MessageTemplateResource
    {
        $template = MessageTemplate::where('template_id', $id)->firstOrFail();

        $template->update($request->validated());

        return MessageTemplateResource::make($template);
    }

    /**
     * Template: delete
     */
    public function destroy(int $id): \Illuminate\Http\Response
    {
        MessageTemplate::where('template_id', $id)->firstOrFail()->delete();

        return response()->noContent();
    }

    /**
     * Template: render with a client's actual data (assembled preview)
     */
    public function render(RenderMessageTemplateRequest $request, int $id, TemplateParamsResolver $resolver): JsonResponse
    {
        $template = MessageTemplate::where('template_id', $id)->firstOrFail();
        $params = $resolver->resolve($request->validated('client_id'));

        return response()->json(['body' => $template->render($params)]);
    }
}
