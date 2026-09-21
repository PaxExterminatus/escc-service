<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        style="width: 60rem"
        @show="render"
    >
        <!-- Слот #header — по тому же образцу, что у HtmlPreviewDialog: кнопка действия рядом
             с заголовком, а не в футере. "Печать" — то же самое, что открывает основная кнопка
             "Печать" на карточке (см. ActionPreviewButton.onMainClick) — открыть исходный файл
             новой вкладкой и напечатать его средствами браузера, а не пытаться распечатать сам
             предпросмотр (он рисуется прямо в DOM диалога, не в iframe — печать текущей
             страницы напечатала бы всё, что вокруг диалога, а не только счёт). -->
        <template #header>
            <div class="flex align-items-center gap-3 flex-1">
                <span class="p-dialog-title">{{ header }}</span>
                <Button v-if="showPrint && url" label="Печать" icon="pi pi-print" size="small" @click="onPrint"/>
            </div>
        </template>

        <LoadingBlock v-if="loading"/>
        <div ref="styleContainer"></div>
        <div ref="bodyContainer"></div>
    </Dialog>
</template>

<script setup>
import {defineEmits, defineProps, ref} from 'vue'
import axios from 'axios'
import {renderAsync} from 'docx-preview'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import LoadingBlock from './LoadingBlockComponent.vue'
import {showError} from 'app/toast'

const props = defineProps({
    visible: {type: Boolean, default: false},
    header: {type: String, default: 'Предпросмотр документа'},
    // Откуда забрать .docx. Читается в момент открытия диалога, не при монтировании.
    url: {type: String, default: null},
    errorMessage: {type: String, default: 'Не удалось загрузить предпросмотр.'},
    showPrint: {type: Boolean, default: false},
});

defineEmits({
    'update:visible': null,
});

const onPrint = () => {
    window.open(props.url, '_blank');
};

const loading = ref(false);
const styleContainer = ref(null);
const bodyContainer = ref(null);

const render = () => {
    if (!props.url) return;

    loading.value = true;
    styleContainer.value.innerHTML = '';
    bodyContainer.value.innerHTML = '';

    axios.get(props.url, {responseType: 'blob'})
        .then((response) => renderAsync(response.data, bodyContainer.value, styleContainer.value))
        .catch(() => {
            showError(props.errorMessage);
        })
        .finally(() => {
            loading.value = false;
        });
};
</script>
