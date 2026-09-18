// GSM 03.38 (7-bit) базовый алфавит — если весь текст укладывается в него, SMS кодируется
// 7 битами на символ (160/153 на часть). Любой символ вне алфавита (кириллица и т.п.) —
// вся SMS уходит в UCS-2 (70/67 на часть), как и в легаси EMESSAGE_CONTENT.PRM_SMSCOUNT.
const GSM_7BIT_BASIC = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ Ææßé !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
// Экранированные символы GSM-7 — каждый занимает 2 позиции (escape + сам символ).
const GSM_7BIT_EXTENDED = '^{}\\[~]|€';

const isGsm7Compatible = (text) => {
    for (const char of text) {
        if (!GSM_7BIT_BASIC.includes(char) && !GSM_7BIT_EXTENDED.includes(char)) {
            return false;
        }
    }

    return true;
};

const gsm7Length = (text) => {
    let length = 0;

    for (const char of text) {
        length += GSM_7BIT_EXTENDED.includes(char) ? 2 : 1;
    }

    return length;
};

/**
 * @param {string} text
 * @return {{encoding: string, length: number, segments: number, singleLimit: number, multiLimit: number}}
 */
const calculateSmsSegments = (text) => {
    const gsm7 = isGsm7Compatible(text);
    const encoding = gsm7 ? 'GSM-7' : 'UCS-2';
    const length = gsm7 ? gsm7Length(text) : [...text].length;
    const singleLimit = gsm7 ? 160 : 70;
    const multiLimit = gsm7 ? 153 : 67;
    const segments = length === 0 ? 0 : (length <= singleLimit ? 1 : Math.ceil(length / multiLimit));

    return {encoding, length, segments, singleLimit, multiLimit};
};

export {
    calculateSmsSegments,
}
