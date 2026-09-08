import {pluralize} from 'app/pluralize'

function parseDateTime(value) {
    if (!value) return null;

    const match = value.match(/(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):(\d{2}))?/);
    if (!match) return null;

    const [, year, month, day, hour = 0, minute = 0, second = 0] = match;
    return new Date(Number(year), Number(month) - 1, Number(day), Number(hour), Number(minute), Number(second));
}

const MS_PER_MINUTE = 60 * 1000;
const MS_PER_HOUR = 60 * MS_PER_MINUTE;
const MS_PER_DAY = 24 * MS_PER_HOUR;

/**
 * Масштабирование по свежести: только что (< 1 мин) → недавно (< 1 ч) →
 * "N часов назад" (< суток) → "N дней" (< года) → "N год/года/лет" (по календарным
 * годам, не по грубому делению на 365, чтобы не съезжало на границе года).
 *
 * @param {string|null} value дата операции, "YYYY-MM-DD HH:MM:SS" (см. ClientFinanceHistoryResource)
 * @return {string|null}
 */
function relativeTimeAgo(value)
{
    const date = parseDateTime(value);
    if (!date) return null;

    const today = new Date();
    const diffMs = today - date;

    if (diffMs < MS_PER_MINUTE) {
        return 'только что';
    }

    if (diffMs < MS_PER_HOUR) {
        return 'недавно';
    }

    if (diffMs < MS_PER_DAY) {
        const hours = Math.floor(diffMs / MS_PER_HOUR);
        return `${hours} ${pluralize(hours, 'час', 'часа', 'часов')} назад`;
    }

    const stripTime = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate());
    const diffDays = Math.round((stripTime(today) - stripTime(date)) / MS_PER_DAY);

    if (diffDays < 365) {
        return `${diffDays} ${pluralize(diffDays, 'день', 'дня', 'дней')}`;
    }

    let years = today.getFullYear() - date.getFullYear();
    const hadAnniversaryThisYear = (today.getMonth() > date.getMonth())
        || (today.getMonth() === date.getMonth() && today.getDate() >= date.getDate());

    if (!hadAnniversaryThisYear) years--;

    return `${years} ${pluralize(years, 'год', 'года', 'лет')}`;
}

export {
    relativeTimeAgo,
}
