<template>
    <TagAwareTextarea
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        :label="label"
        :disabled="disabled"
        :id="id"
        :client-id="clientId"
    >
        <template #footer>
            <div>
                <Button
                    label="Предпросмотр письма"
                    icon="pi pi-eye"
                    size="small"
                    text
                    :loading="previewLoading"
                    @click="openPreview"
                />
            </div>
        </template>
    </TagAwareTextarea>

    <!--
        showSend: письмо уже видно оператору — логично дать отправить сразу отсюда, не
        закрывая предпросмотр отдельным кликом. @send пробрасывается родителю (см.
        MessagesPanelComponent) — там же живёт "стандартная" кнопка "Отправить", её и
        вызывает эта же логика, просто без лишнего клика по предпросмотру.
    -->
    <HtmlPreviewDialog v-model:visible="previewVisible" :html="previewHtml" show-send @send="$emit('send')"/>
</template>

<script setup>
import {defineEmits, defineProps, ref} from 'vue'
import Button from 'primevue/button'
import TagAwareTextarea from './TagAwareTextareaComponent.vue'
import {HtmlPreviewDialog} from 'element'
import {templateAPI} from 'cmp/templates'
import {showError} from 'app/toast'

const props = defineProps({
    modelValue: {type: String, default: ''},
    label: {type: String, default: 'Текст письма (плейсхолдеры вида {amount})'},
    disabled: {type: Boolean, default: false},
    id: {type: String, default: 'emailBody'},
    clientId: {type: [String, Number], default: null},
    // Выбранный шаблон задаёт обёртку письма — предпросмотр должен показать именно её
    templateId: {type: [String, Number], default: null},
});

defineEmits({
    'update:modelValue': null,
    send: null,
});

const previewVisible = ref(false);
const previewLoading = ref(false);
const previewHtml = ref('');

const openPreview = () => {
    previewLoading.value = true;

    templateAPI.emailPreview(props.clientId, props.modelValue ?? '', props.templateId)
        .then((response) => {
            previewHtml.value = response.data.html;
            previewVisible.value = true;
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось собрать предпросмотр письма.'))
        .finally(() => previewLoading.value = false);
};
</script>
