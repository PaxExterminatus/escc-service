<template>
    <Toolbar>
        <template #start>
            <h1>Массовые рассылки — SMS на сегодня</h1>
        </template>
        <template #end>
            <a :href="txtUrl" target="_blank" class="p-button p-button-text mr-2">
                <span class="pi pi-download mr-2"></span>Скачать .txt
            </a>
            <Button label="Обновить" icon="pi pi-refresh" severity="secondary" outlined class="mr-2" :loading="loading" @click="load"/>
            <Button label="Отправить" icon="pi pi-send" :disabled="!messages.length" :loading="sending" @click="send"/>
        </template>
    </Toolbar>

    <DataTable :value="messages" :loading="loading" dataKey="id">
        <Column field="id" header="ID"/>
        <Column field="address" header="Адрес"/>
        <Column field="body" header="Текст"/>
    </DataTable>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import Toolbar from 'primevue/toolbar'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {showError, showSuccess} from 'app/toast'
import {messagingAPI} from 'cmp/messages'

const type = 'sms';

const messages = ref([]);
const loading = ref(false);
const sending = ref(false);
const txtUrl = messagingAPI.dailyTxtUrl(type);

const load = () => {
    loading.value = true;

    messagingAPI.daily(type)
        .then((response) => {
            messages.value = response.data.messages;
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось загрузить список.');
        })
        .finally(() => {
            loading.value = false;
        });
};

onMounted(load);

const send = () => {
    sending.value = true;

    messagingAPI.dailySend(type)
        .then((response) => {
            const status = response.data.response?.status;
            showSuccess(`Отправлено. Статус ответа шлюза: ${status}.`);
            load();
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось отправить рассылку.');
        })
        .finally(() => {
            sending.value = false;
        });
};
</script>
