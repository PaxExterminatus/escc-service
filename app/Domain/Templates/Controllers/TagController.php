<?php

namespace App\Domain\Templates\Controllers;

use App\Domain\App\Container\Models\Container;
use App\Domain\Templates\Services\TagResolver;
use App\Domain\Templates\TagDefinition;
use App\Domain\Templates\TagRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Список тегов-плейсхолдеров, доступных при составлении шаблонов сообщений и документов —
 * этим же API пользуется внешнее Word-расширение для показа списка тегов с описанием.
 */
class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = collect(TagRegistry::all())->map(fn (TagDefinition $tag) => [
            'code' => $tag->code,
            'description' => $tag->description,
            'category' => $tag->category->value,
            'category_label' => $tag->category->label(),
            'scope' => $tag->scope->value,
            'scope_label' => $tag->scope->label(),
        ]);

        return response()->json(['tags' => $tags]);
    }

    /**
     * Готовые значения тегов уровня "Счёт" для конкретного контейнера — используется,
     * когда оператор в редакторе сообщения выбрал контейнер через пикер (курс → контейнер)
     * и нужно сразу подставить реальное значение тега, а не плейсхолдер.
     */
    public function forContainer(int $id, TagResolver $resolver): JsonResponse
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        return response()->json(['tags' => $resolver->resolveForContainer($container)]);
    }
}
