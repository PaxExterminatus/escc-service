<?php

namespace App\Domain\Templates\Controllers;

use App\Domain\Templates\Enums\TemplateOperationEnum;
use App\Domain\Templates\Enums\TemplateTypeEnum;
use App\Domain\Templates\Models\Template;
use App\Domain\Templates\Requests\AssignOperationTemplateRequest;
use App\Domain\Templates\Requests\EmailPreviewRequest;
use App\Domain\Templates\Requests\RenderTemplateRequest;
use App\Domain\Templates\Requests\StoreTemplateRequest;
use App\Domain\Templates\Requests\UpdateTemplateRequest;
use App\Domain\Templates\Resources\TemplateResource;
use App\Domain\Templates\Services\EmailComposer;
use App\Domain\Templates\Services\TagInterpolator;
use App\Domain\Templates\Services\TagResolver;
use App\Domain\Templates\Services\TemplateResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Шаблоны — единая сущность для того, что раньше было двумя разными: текстовый шаблон
 * сообщения (SMS/Email) и docx-шаблон документа. Здесь три разных набора действий:
 *  - "самостоятельные" шаблоны сообщений, TYPE_ID = text (OPERATION_ID = null) — библиотека,
 *    из которой оператор вручную выбирает шаблон при отправке (index/store/update/destroy/render);
 *  - шаблоны, привязанные к операции (счёт-документ, счёт-письмо) — ровно один на пару
 *    (операция, носитель), назначаются через operations()/assignOperation();
 *  - обёртки писем, TYPE_ID = wrapper (OPERATION_ID = null) — тоже свободная библиотека
 *    (store/update/destroy те же, что и для текстовых, различаются по TYPE_ID), см. wrappers().
 */
class TemplateController extends Controller
{
    protected const DISK_DIRECTORY = 'templates';

    /**
     * Самостоятельные шаблоны сообщений (SMS/Email) — список. Обёртки писем сюда не входят,
     * см. wrappers() — это разные библиотеки с разным назначением.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $templates = Template::free()
            ->ofType(TemplateTypeEnum::text)
            ->when($request->boolean('active_only'), fn ($query) => $query->where('is_active', 1))
            ->orderBy('name')
            ->get();

        return TemplateResource::collection($templates);
    }

    /**
     * Библиотека обёрток письма (шапка+футер) — из них content-шаблоны (свободные email/
     * "Счёт по email") выбирают себе одну через WRAPPER_ID, см. Template::wrappers().
     */
    public function wrappers(Request $request): AnonymousResourceCollection
    {
        return TemplateResource::collection(Template::wrappers($request->boolean('active_only')));
    }

    public function store(StoreTemplateRequest $request): TemplateResource
    {
        $data = $request->validated();
        $typeId = $data['type_id'] ?? TemplateTypeEnum::text->value;

        $attributes = [
            'type_id' => $typeId,
            'code' => $data['code'],
            'name' => $data['name'],
            'body' => $data['body'],
            'is_active' => $data['is_active'] ?? true,
        ];

        if ($typeId === TemplateTypeEnum::wrapper->value) {
            $attributes['is_default'] = $data['is_default'] ?? false;
            $attributes['wrapper_role'] = $data['wrapper_role'] ?? null;
        } else {
            $attributes['wrapper_auto'] = $data['wrapper_auto'] ?? false;
            $attributes['wrapper_id'] = $attributes['wrapper_auto'] ? null : ($data['wrapper_id'] ?? null);
        }

        return DB::connection('oracle')->transaction(function () use ($attributes, $typeId) {
            if ($typeId === TemplateTypeEnum::wrapper->value && ($attributes['is_default'] ?? false)) {
                $this->clearDefaultWrapper();
            }

            return TemplateResource::make(Template::create($attributes));
        });
    }

    public function update(UpdateTemplateRequest $request, int $id): TemplateResource
    {
        $template = Template::free()->where('template_id', $id)->firstOrFail();
        $data = $request->validated();

        if ($template->type_id === TemplateTypeEnum::wrapper) {
            $data['is_default'] = $data['is_default'] ?? false;
            $data['wrapper_role'] = $data['wrapper_role'] ?? null;
            unset($data['wrapper_id'], $data['wrapper_auto']);
        } else {
            // Ключи нормализуем явно: без этого "сбросить обёртку на умолчание" невозможно —
            // отсутствующий в запросе wrapper_id/wrapper_auto оставлял бы прежнее значение.
            $data['wrapper_auto'] = $data['wrapper_auto'] ?? false;
            $data['wrapper_id'] = $data['wrapper_auto'] ? null : ($data['wrapper_id'] ?? null);
            unset($data['is_default'], $data['wrapper_role']);
        }

        DB::connection('oracle')->transaction(function () use ($template, $data, $id) {
            if (($data['is_default'] ?? false) && $template->type_id === TemplateTypeEnum::wrapper) {
                $this->clearDefaultWrapper($id);
            }

            $template->update($data);
        });

        return TemplateResource::make($template->fresh());
    }

    public function destroy(int $id): \Illuminate\Http\Response
    {
        $template = Template::free()->where('template_id', $id)->firstOrFail();

        // На WRAPPER_ID стоит внешний ключ: без этой проверки удаление используемой обёртки
        // вернуло бы оператору ORA-02292 вместо внятного объяснения.
        $usedBy = Template::where('wrapper_id', $id)->count();

        abort_if($usedBy > 0, 422, "Обёртка используется в шаблонах ({$usedBy} шт.) — сначала выберите им другую.");

        $template->delete();

        return response()->noContent();
    }

    /**
     * Гарантирует единственность IS_DEFAULT среди обёрток — снимает флаг со всех остальных
     * перед тем, как новая обёртка его получит.
     */
    protected function clearDefaultWrapper(?int $exceptId = null): void
    {
        Template::ofType(TemplateTypeEnum::wrapper)
            ->when($exceptId, fn ($query) => $query->where('template_id', '!=', $exceptId))
            ->update(['is_default' => 0]);
    }

    /**
     * Самостоятельный шаблон: собрать с реальными данными клиента (предпросмотр)
     */
    public function render(RenderTemplateRequest $request, int $id, TagResolver $resolver): JsonResponse
    {
        $template = Template::free()->where('template_id', $id)->firstOrFail();
        $params = $resolver->resolveForClient($request->validated('client_id'));

        return response()->json(['body' => $template->render($params)]);
    }

    /**
     * Предпросмотр email-сообщения из панели отправки: обычный текст (с уже подставленными
     * или ещё не разрешёнными {tag}) оборачивается в обёртку письма — оператор видит письмо
     * ровно так, как оно уйдёт получателю.
     */
    public function emailPreview(EmailPreviewRequest $request, TagResolver $resolver, EmailComposer $composer): JsonResponse
    {
        $data = $request->validated();
        $params = $resolver->resolveForClient($data['client_id']);

        $template = empty($data['template_id']) ? null : Template::free()->where('template_id', $data['template_id'])->first();

        $body = TagInterpolator::apply($data['body'] ?? '', $params);

        return response()->json(['html' => $composer->composeFromText($body, $params, $template?->wrapper_id, $template?->wrapper_auto ?? false)]);
    }

    /**
     * Предпросмотр любого шаблона — как его увидит получатель. Теги остаются неразрешёнными:
     * конкретного клиента у шаблона нет, оператор смотрит вёрстку (см. EmailComposer::preview).
     */
    public function preview(int $id, EmailComposer $composer): JsonResponse
    {
        $template = Template::where('template_id', $id)->firstOrFail();

        return response()->json(['html' => $composer->preview($template)]);
    }

    /**
     * Операции и назначенные им шаблоны (по каждому применимому носителю)
     */
    public function operations(): JsonResponse
    {
        $templates = Template::whereNotNull('operation_id')->get()
            ->groupBy('operation_id');

        $operations = collect(TemplateOperationEnum::cases())->map(function (TemplateOperationEnum $operation) use ($templates) {
            $rows = $templates->get($operation->value, collect());

            return [
                'operation_id' => $operation->value,
                'group_label' => $operation->groupLabel(),
                'slots' => collect($operation->applicableTypes())->map(function (TemplateTypeEnum $type) use ($operation, $rows) {
                    $assigned = $rows->firstWhere('type_id', $type);

                    return [
                        'type_id' => $type->value,
                        'type_label' => $type->label(),
                        'operation_label' => $operation->label($type),
                        'has_default' => $operation->hasBuiltInDefault($type),
                        'template' => $assigned ? TemplateResource::make($assigned)->resolve() : null,
                    ];
                }),
            ];
        });

        return response()->json(['operations' => $operations]);
    }

    /**
     * Назначить шаблон операции для конкретного носителя — заменяет предыдущий, если был
     */
    public function assignOperation(AssignOperationTemplateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $type = TemplateTypeEnum::from($data['type_id']);

        $attributes = ['name' => $data['name']];

        if ($type === TemplateTypeEnum::docx) {
            $storedName = TemplateOperationEnum::from($data['operation_id'])->name . '.docx';
            $relativePath = self::DISK_DIRECTORY . '/' . $storedName;
            Storage::putFileAs(self::DISK_DIRECTORY, $data['file'], $storedName);

            $attributes['filename'] = $relativePath;
            $attributes['uploaded_at'] = now();
        } else {
            $attributes['body'] = $data['body'];
            $attributes['wrapper_auto'] = $data['wrapper_auto'] ?? false;
            $attributes['wrapper_id'] = $attributes['wrapper_auto'] ? null : ($data['wrapper_id'] ?? null);
        }

        $template = Template::updateOrCreate(
            ['operation_id' => $data['operation_id'], 'type_id' => $data['type_id']],
            $attributes + ['code' => $this->operationCode($data['operation_id'], $data['type_id'])]
        );

        return response()->json(['template' => TemplateResource::make($template)->resolve()]);
    }

    /**
     * Файл docx-шаблона операции — для предпросмотра (docx-preview забирает его как blob,
     * Content-Disposition ему не важен) и для скачивания оператором на правку во внешнем
     * редакторе (Word), см. templateAPI.operationFileUrl().
     */
    public function operationFile(int $operationId, TemplateResolver $resolver): BinaryFileResponse
    {
        $operation = TemplateOperationEnum::tryFrom($operationId);

        abort_if($operation === null, 404, 'Неизвестная операция.');

        return response()->download($resolver->docxPathFor($operation), $operation->name . '.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /** Код шаблона операции — служебный, оператор его не вводит и не видит */
    protected function operationCode(int $operationId, int $typeId): string
    {
        return 'op_' . $operationId . '_' . $typeId;
    }
}
