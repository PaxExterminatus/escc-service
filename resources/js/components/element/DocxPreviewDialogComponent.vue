<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :header="header"
        style="width: 60rem"
        @show="render"
    >
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
import LoadingBlock from './LoadingBlockComponent.vue'
import {showError} from 'app/toast'

const props = defineProps({
    visible: {type: Boolean, default: false},
    header: {type: String, default: 'Предпросмотр документа'},
    // Откуда забрать .docx. Читается в момент открытия диалога, не при монтировании.
    url: {type: String, default: null},
    errorMessage: {type: String, default: 'Не удалось загрузить предпросмотр.'},
});

defineEmits({
    'update:visible': null,
});

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
