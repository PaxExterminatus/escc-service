import ToastEventBus from 'primevue/toasteventbus'

/**
 * Показать тост с ошибкой. ToastEventBus (а не useToast()) — потому что вызывается
 * не только из компонентов (где есть Vue-контекст), но и из глобального
 * axios-перехватчика в app.js, где useToast() недоступен.
 */
function showError(message) {
    ToastEventBus.emit('add', {
        severity: 'error',
        summary: 'Ошибка',
        detail: message,
        life: 5000,
    });
}

export {
    showError,
}
