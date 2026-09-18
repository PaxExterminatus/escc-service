<?php

namespace App\Domain\Templates\Models;

use App\Domain\Templates\Enums\TemplateOperationEnum;
use App\Domain\Templates\Enums\TemplateTypeEnum;
use App\Domain\Templates\Enums\TemplateWrapperRoleEnum;
use App\Domain\Templates\Services\TagInterpolator;
use Illuminate\Support\Collection;
use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * Единый шаблон — заменяет собой то, что раньше было двумя разными сущностями
 * (текстовый шаблон сообщения и docx-шаблон документа): по сути одна и та же идея
 * "шаблон с тегами", разные носители (см. TemplateTypeEnum).
 *
 * OPERATION_ID = null — самостоятельный шаблон (свободный шаблон сообщения ИЛИ обёртка письма,
 * различаются по TYPE_ID). OPERATION_ID задан — шаблон привязан к операции
 * (см. TemplateOperationEnum): печать/письмо по счёту.
 *
 * WRAPPER_ID — у text/html-содержимого ссылка на конкретную обёртку письма (см. wrappers()),
 * NULL — использовать обёртку с IS_DEFAULT = 1.
 */
class Template extends OracleEloquent
{
    protected $table = 'SERVICE_TEMPLATE';
    protected $primaryKey = 'template_id';
    public $sequence = 'SERVICE_TEMPLATE_SEQ';
    public $timestamps = false;

    protected $fillable = [
        'type_id', 'operation_id', 'code', 'name', 'body', 'filename',
        'wrapper_id', 'wrapper_auto', 'wrapper_role', 'is_default', 'is_active', 'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'template_id' => 'integer',
            'type_id' => TemplateTypeEnum::class,
            'operation_id' => 'integer',
            'wrapper_id' => 'integer',
            'wrapper_auto' => 'boolean',
            'wrapper_role' => TemplateWrapperRoleEnum::class,
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    /**
     * Шаблон, назначенный операции для данного носителя (docx для печати, html для письма) —
     * по одному на пару (операция, носитель), см. UNIQUE в миграции.
     */
    public static function forOperation(TemplateOperationEnum $operation, TemplateTypeEnum $type): ?self
    {
        return static::where('operation_id', $operation->value)
            ->where('type_id', $type->value)
            ->first();
    }

    /** Шаблоны одного носителя — база для запросов по обёрткам и свободным шаблонам */
    public function scopeOfType($query, TemplateTypeEnum $type)
    {
        return $query->where('type_id', $type->value);
    }

    /** Самостоятельные шаблоны: не привязаны к операции (свободные и обёртки) */
    public function scopeFree($query)
    {
        return $query->whereNull('operation_id');
    }

    /**
     * Библиотека обёрток письма (шапка+футер, спецтег {BODY}) — свободные шаблоны типа wrapper,
     * из которых text/html-контент выбирает себе одну через WRAPPER_ID (см. EmailComposer::wrap).
     */
    public static function wrappers(bool $activeOnly = false): Collection
    {
        return static::ofType(TemplateTypeEnum::wrapper)
            ->when($activeOnly, fn ($query) => $query->where('is_active', 1))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    /**
     * Конкретная обёртка, пригодная для отправки. Неактивную намеренно не отдаём — иначе
     * выключенная обёртка продолжала бы уходить клиентам (см. EmailComposer::wrap).
     */
    public static function activeWrapper(int $id): ?self
    {
        return static::ofType(TemplateTypeEnum::wrapper)
            ->where('template_id', $id)
            ->where('is_active', 1)
            ->first();
    }

    public static function defaultWrapper(): ?self
    {
        return static::ofType(TemplateTypeEnum::wrapper)
            ->where('is_default', 1)
            ->where('is_active', 1)
            ->first();
    }

    /** Обёртка для авто-выбора по балансу (см. EmailComposer::wrapperForBalance) */
    public static function wrapperForRole(TemplateWrapperRoleEnum $role): ?self
    {
        return static::ofType(TemplateTypeEnum::wrapper)
            ->where('wrapper_role', $role->value)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Тело шаблона как html: текстовые шаблоны экранируются и их переносы строк становятся
     * <br>, у html-шаблонов и обёрток тело уже является разметкой.
     */
    public function contentHtml(): string
    {
        $body = $this->body ?? '';

        return $this->type_id === TemplateTypeEnum::text ? nl2br(e($body)) : $body;
    }

    /**
     * Подставляет значения тегов (см. TagResolver) в тело шаблона. Спецтег {BODY} не трогает —
     * он структурный, его обрабатывает EmailComposer при сборке письма.
     *
     * Для html-носителей значения экранируются: имя клиента с «<» или «&» иначе сломало бы
     * вёрстку письма. В docx/текст подставляем как есть — там это просто символы.
     *
     * @param array<string, string> $params
     */
    public function render(array $params): string
    {
        $isHtml = $this->type_id === TemplateTypeEnum::html || $this->type_id === TemplateTypeEnum::wrapper;

        return TagInterpolator::apply($this->body ?? '', $params, $isHtml);
    }
}
