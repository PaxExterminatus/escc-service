import {pluralize} from 'app/pluralize'

function parseBirthday(value) {
    if (!value) return null;

    const [day, month, year] = value.split('.').map(Number);
    if (!day || !month || !year) return null;

    return new Date(year, month - 1, day);
}

function calculateAge(birthday, today) {
    let age = today.getFullYear() - birthday.getFullYear();
    const hadBirthdayThisYear = (today.getMonth() > birthday.getMonth())
        || (today.getMonth() === birthday.getMonth() && today.getDate() >= birthday.getDate());

    if (!hadBirthdayThisYear) age--;

    return age;
}

function daysUntilOrSinceBirthday(birthday, today) {
    const stripTime = (date) => new Date(date.getFullYear(), date.getMonth(), date.getDate());

    const todayStripped = stripTime(today);
    const thisYearBirthday = new Date(today.getFullYear(), birthday.getMonth(), birthday.getDate());

    const diffDays = Math.round((thisYearBirthday - todayStripped) / 86400000);

    if (diffDays === 0) return {days: 0, type: 'today'};
    if (diffDays > 0) return {days: diffDays, type: 'until'};

    return {days: -diffDays, type: 'since'};
}

/**
 * @param {string|null} value дата в формате dd.mm.yyyy (см. ProfileResource::toArray -> birthday)
 * @return {{age: string, countdown: string}|null}
 */
function birthdayInfo(value)
{
    const birthday = parseBirthday(value);
    if (!birthday) return null;

    const today = new Date();

    const age = calculateAge(birthday, today);
    const {days, type} = daysUntilOrSinceBirthday(birthday, today);

    let countdown;
    if (type === 'today') {
        countdown = 'ДР сегодня!';
    } else if (type === 'until') {
        countdown = `ДР через ${days} ${pluralize(days, 'день', 'дня', 'дней')}`;
    } else {
        countdown = `ДР ${days} ${pluralize(days, 'день', 'дня', 'дней')} назад`;
    }

    return {
        age: `${age} ${pluralize(age, 'год', 'года', 'лет')}`,
        countdown,
    };
}

export {
    birthdayInfo,
}
