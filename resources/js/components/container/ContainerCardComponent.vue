<template>
    <Card class="w-full">
        <template #title></template>
        <template #content>
            <InputGroup>
                <Input v-model="container().id" @enter="search" id="containerId" label="ID"/>
                <SplitButton :label="container().status ?? 'Статус'" :model="statusActions" outlined/>
            </InputGroup>

            <InputGroup>
                <Input v-model="container().client_id" id="containerClientId" label="Client ID"/>
                <Input v-model="container().code" id="containerCode" label="Код"/>
                <Input v-model="container().created_at" id="containerCreatedAt" label="Дата создания"/>
            </InputGroup>

            <Fieldset v-if="container().status === 'Sent'" legend="Счёт контейнера" class="mt-3">
                <div class="flex flex-row flex-wrap gap-2">
                    <ActionPreviewButton
                        label="Печать"
                        icon="pi pi-file-pdf"
                        :href="invoiceUrl"
                        tooltip="Счёт выставляется в момент отправки контейнера"
                        :preview-loading="printPreviewLoading"
                        preview-tooltip="Просмотр документа"
                        @preview="openPrintPreview"
                    />

                    <ActionPreviewButton
                        label="Email"
                        icon="pi pi-envelope"
                        :loading="sendingEmail"
                        tooltip="Отправить клиенту на email"
                        :preview-loading="emailPreviewLoading"
                        preview-tooltip="Просмотр письма"
                        @click="sendEmail"
                        @preview="openEmailPreview"
                    />
                </div>
            </Fieldset>
        </template>
    </Card>

    <DocxPreviewDialog
        v-model:visible="printPreviewVisible"
        header="Предпросмотр счёта"
        :url="invoiceUrl"
        error-message="Не удалось загрузить предпросмотр счёта."
    />

    <!--
        showSend: диалог просто закрывается и вызывает ту же sendEmail(), что и обычная
        кнопка "Email" — закрытие предпросмотра не прерывает уже запущенную отправку, т.к.
        промис отправки живёт здесь, а не в диалоге.
    -->
    <HtmlPreviewDialog
        v-model:visible="emailPreviewVisible"
        :html="emailPreviewContent"
        :loading="emailPreviewLoading"
        show-send
        @send="sendEmail"
    />
</template>

<script setup>
import {computed, defineEmits, defineProps, ref} from 'vue'
import Card from 'primevue/card'
import Fieldset from 'primevue/fieldset'
import InputGroup from 'primevue/inputgroup'
import SplitButton from 'primevue/splitbutton'
import {ActionPreviewButton, DocxPreviewDialog, HtmlPreviewDialog, Input} from 'element'
import {Container} from './Container.js'
import {invoiceAPI} from 'cmp/invoice'
import {showError, showSuccess} from 'app/toast'

const props = defineProps({
    container: Container,
});

/** @return {Container} */
const container = () => {
    return props.container;
}

const emit = defineEmits({
    search: null,
    stop: null,
    start: null,
    setStatus: null,
});

const search = () => {
    emit('search');
};

const invoiceUrl = computed(() => invoiceAPI.containerInvoiceUrl(container().id));

const sendingEmail = ref(false);

const sendEmail = () => {
    sendingEmail.value = true;

    invoiceAPI.sendContainerInvoiceEmail(container().id)
        .then((response) => {
            const status = response.data.response?.status;

            if (status === 200) {
                showSuccess('Письмо со счётом отправлено.');
            } else {
                showError(`Не удалось отправить письмо: ${response.data.response?.reason ?? status}`);
            }
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось отправить письмо.');
        })
        .finally(() => {
            sendingEmail.value = false;
        });
};

// --- Предпросмотр счёта: документ отдаётся файлом, письмо собирает сервер ---

const printPreviewVisible = ref(false);
const printPreviewLoading = ref(false);

const openPrintPreview = () => {
    printPreviewVisible.value = true;
};

const emailPreviewVisible = ref(false);
const emailPreviewLoading = ref(false);
const emailPreviewContent = ref('');

const openEmailPreview = () => {
    emailPreviewVisible.value = true;
    emailPreviewLoading.value = true;

    invoiceAPI.containerInvoiceEmailPreview(container().id)
        .then((response) => {
            emailPreviewContent.value = response.data.html;
        })
        .catch((error) => {
            emailPreviewVisible.value = false;
            showError(error.response?.data?.message ?? 'Не удалось загрузить предпросмотр письма.');
        })
        .finally(() => {
            emailPreviewLoading.value = false;
        });
};

// Полный список статусов — App\Domain\App\Container\Enums\ContainerStatusEnum (id из REV_CONST.boxStatus*).
// Stopped/InProgress — через отдельные "stop"/"start" (там же простая семантика "остановить/запустить"),
// остальные — через общий "setStatus" с конкретным id.
const statusActions = [
    {
        label: 'Остановить [1]',
        command: () => emit('stop'),
    },
    {
        label: 'Запустить [2]',
        command: () => emit('start'),
    },
    {
        label: 'Error [3]',
        command: () => emit('setStatus', 3),
    },
    {
        label: 'Canceled [4]',
        command: () => emit('setStatus', 4),
    },
    {
        label: 'Assembling [50]',
        command: () => emit('setStatus', 50),
    },
    {
        label: 'Ready [45]',
        command: () => emit('setStatus', 45),
    },
    {
        label: 'Sent [70]',
        command: () => emit('setStatus', 70),
    },
    {
        label: 'Temporary [-1]',
        command: () => emit('setStatus', -1),
    },
];
</script>
