<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        style="width: 45rem"
    >
        <!--
            Слот #header заменяет только заголовок — кнопка закрытия (крестик) рисуется
            PrimeVue отдельно и остаётся на месте. Кнопка "Отправить" стоит прямо здесь, а не
            в футере: закрывает диалог и тут же вызывает send — сама отправка (и её спиннер)
            живёт у родителя на "стандартной" кнопке "Отправить", закрытие диалога её не прерывает.
        -->
        <template #header>
            <div class="flex align-items-center gap-3 flex-1">
                <span class="p-dialog-title">{{ header }}</span>
                <Button v-if="showSend" :label="sendLabel" icon="pi pi-send" size="small" @click="onSend"/>
            </div>
        </template>

        <LoadingBlock v-if="loading"/>
        <iframe v-else :srcdoc="html" class="html-preview-frame"></iframe>
    </Dialog>
</template>

<script setup>
import {defineEmits, defineProps} from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import LoadingBlock from './LoadingBlockComponent.vue'

defineProps({
    visible: {type: Boolean, default: false},
    header: {type: String, default: 'Предпросмотр письма'},
    // Готовый html — собирается сервером (см. EmailComposer), тут только показывается
    html: {type: String, default: ''},
    loading: {type: Boolean, default: false},
    showSend: {type: Boolean, default: false},
    sendLabel: {type: String, default: 'Отправить'},
});

const emit = defineEmits({
    'update:visible': null,
    send: null,
});

const onSend = () => {
    emit('update:visible', false);
    emit('send');
};
</script>

<style scoped>
/* Письмо рисуется в iframe, чтобы его собственные стили не смешивались с темой приложения */
.html-preview-frame {
    width: 100%;
    height: 28rem;
    border: none;
    background: #fff;
}
</style>
