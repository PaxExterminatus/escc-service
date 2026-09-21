<template>
    <div class="flex flex-column md:flex-row gap-3">
        <Fieldset legend="Печать" class="flex-1">
            <ActionPreviewButton
                label="Печать"
                icon="pi pi-file-pdf"
                :href="invoiceUrl"
                tooltip="Счёт выставляется в момент отправки контейнера"
                :preview-loading="printPreviewLoading"
                preview-tooltip="Просмотр документа"
                @preview="openPrintPreview"
            />
        </Fieldset>

        <Fieldset legend="На почту" class="flex-1">
            <div class="flex flex-column gap-3">
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

                <!-- Куда реально уйдёт письмо и есть ли на это согласие — видно до клика,
                     не постфактум по ошибке отправки. -->
                <div class="flex align-items-center gap-2 text-sm text-color-secondary">
                    <span class="pi pi-envelope"></span>
                    <span>{{ container.client_email ?? 'Email не указан' }}</span>
                    <Tag
                        :severity="container.email_allowed ? 'success' : 'danger'"
                        :value="container.email_allowed ? 'Согласие есть' : 'Нет согласия'"
                        v-tooltip.top="container.email_allowed ? 'Клиент дал согласие на email' : 'Клиент не дал согласие на email'"
                    />
                    <router-link
                        :to="{name: 'clientsProfile', params: {id: container.client_id}}"
                        class="p-button p-button-text p-button-rounded p-button-sm ml-auto"
                        v-tooltip.top="'Открыть профиль клиента'"
                    >
                        <span class="pi pi-user"></span>
                    </router-link>
                </div>
            </div>
        </Fieldset>
    </div>

    <DocxPreviewDialog
        v-model:visible="printPreviewVisible"
        header="Предпросмотр счёта"
        :url="invoiceUrl"
        error-message="Не удалось загрузить предпросмотр счёта."
        show-print
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
import {computed, defineProps, ref} from 'vue'
import Fieldset from 'primevue/fieldset'
import Tag from 'primevue/tag'
import {ActionPreviewButton, DocxPreviewDialog, HtmlPreviewDialog} from 'element'
import {Container} from './Container.js'
import {invoiceAPI} from 'cmp/invoice'
import {showError, showSuccess} from 'app/toast'

const props = defineProps({
    container: Container,
});

const invoiceUrl = computed(() => invoiceAPI.containerInvoiceUrl(props.container.id));

const sendingEmail = ref(false);

const sendEmail = () => {
    sendingEmail.value = true;

    invoiceAPI.sendContainerInvoiceEmail(props.container.id)
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

    invoiceAPI.containerInvoiceEmailPreview(props.container.id)
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
</script>
