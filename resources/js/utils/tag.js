/**
 * Работа с тегами-плейсхолдерами в теле шаблона.
 *
 * Правила вставки были разбросаны по четырём компонентам и успели разойтись (где-то пробел
 * добавлялся, где-то нет) — держим их здесь в одном месте.
 */

/**
 * Плейсхолдер тега в том виде, в каком он подставляется в текст: {client_name}.
 *
 * @param {string} code
 * @return {string}
 */
function tagPlaceholder(code) {
    return `{${code}}`;
}

/**
 * Дописывает значение в конец текста, отделяя пробелом, если его там ещё нет.
 *
 * @param {string} body
 * @param {string} value
 * @return {string}
 */
function appendTag(body, value) {
    const text = body ?? '';

    return text && !text.endsWith(' ') ? `${text} ${value}` : `${text}${value}`;
}

export {
    tagPlaceholder,
    appendTag,
}
