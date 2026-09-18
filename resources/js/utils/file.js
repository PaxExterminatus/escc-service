/**
 * Скачивание/загрузка текстового содержимого как файла — для редактирования шаблона во
 * внешнем редакторе без похода на сервер: текущее (в т.ч. ещё не сохранённое) содержимое
 * поля уходит в файл, а после правок обратно читается тем же способом, каким читаются файлы
 * из <input type="file"> где угодно ещё в приложении.
 */

/**
 * Отдаёт браузеру текст как файл для скачивания (сохранение через диалог "Сохранить как").
 *
 * @param {string} filename
 * @param {string} content
 * @param {string} mime
 */
function downloadText(filename, content, mime = 'text/html') {
    const url = URL.createObjectURL(new Blob([content], {type: mime}));
    const link = document.createElement('a');

    link.href = url;
    link.download = filename;
    link.click();

    URL.revokeObjectURL(url);
}

/**
 * Читает текстовое содержимое выбранного пользователем файла (событие change у input[type=file]).
 *
 * @param {Event} event
 * @return {Promise<string>}
 */
function readTextFile(event) {
    const file = event.target.files[0];

    if (!file) return Promise.resolve(null);

    return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(reader.error);
        reader.readAsText(file);
    });
}

export {
    downloadText,
    readTextFile,
}
