<?php

namespace App\Domain\Templates\Services;

/**
 * Разметка обёртки письма (шапка + футер вокруг спецтега {BODY}).
 *
 * Обёртки — обычные редактируемые шаблоны в SERVICE_TEMPLATE (TYPE_ID = wrapper), оператор правит их
 * в интерфейсе. Здесь лежит только исходная вёрстка, от которой они стартуют: её использует
 * TemplateSeeder при первичном наполнении и EmailComposer как аварийный запасной вариант,
 * если в системе не оказалось ни одной обёртки. Один источник вёрстки на оба случая — чтобы
 * "стандартное письмо ЕШКО" не пришлось править в двух местах.
 */
class EmailWrapperLayout
{
    /** Нейтральный синий — обёртка по умолчанию (счета, уведомления о посылке) */
    public const NEUTRAL = ['#1d5b8a', '#c6dcf2'];

    /** Красный — для сообщений о задолженности */
    public const DEBT = ['#b3261e', '#f2c9c6'];

    /** Зелёный — для клиентов без задолженности */
    public const POSITIVE = ['#2c6e49', '#c6e6d3'];

    /**
     * @param array{string, string} $palette одна из констант класса: [цвет шапки, цвет рамки]
     */
    public static function build(array $palette): string
    {
        [$accent, $border] = $palette;

        return <<<HTML
            <div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; color: #333333;">
                <div style="background: {$accent}; padding: 20px 28px; border-radius: 8px 8px 0 0;">
                    <span style="color: #ffffff; font-size: 20px; font-weight: bold; letter-spacing: 0.5px;">ЕШКО</span>
                </div>
                <div style="background: #ffffff; padding: 24px 28px; border: 1px solid {$border}; border-top: none; line-height: 1.5;">
                    {BODY}
                </div>
                <div style="background: #f7f7f7; padding: 16px 28px; border-radius: 0 0 8px 8px; border: 1px solid {$border}; border-top: none; font-size: 12px; color: #888888;">
                    С уважением, команда ЕШКО.<br>
                    Это письмо сформировано автоматически, отвечать на него не нужно.
                </div>
            </div>
            HTML;
    }
}
