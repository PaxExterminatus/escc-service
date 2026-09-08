/**
 * Русское склонение числительных: pluralize(n, 'день', 'дня', 'дней').
 */
function pluralize(n, one, few, many) {
    const mod100 = Math.abs(n) % 100;
    const mod10 = mod100 % 10;

    if (mod100 > 10 && mod100 < 20) return many;
    if (mod10 === 1) return one;
    if (mod10 >= 2 && mod10 <= 4) return few;

    return many;
}

export {
    pluralize,
}
