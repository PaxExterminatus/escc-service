<template>
    <Toolbar>
        <template #start>
            <h1>Поиск контейнеров</h1>
        </template>
    </Toolbar>

    <div class="filters-grid my-3">
        <FloatLabel>
            <InputText v-model="filters.container_code" id="filterContainerCode" class="w-full" @keyup.enter="search"/>
            <label for="filterContainerCode">Код контейнера</label>
        </FloatLabel>

        <FloatLabel>
            <InputText v-model.number="filters.client_id" id="filterClientId" class="w-full" @keyup.enter="search"/>
            <label for="filterClientId">ID клиента</label>
        </FloatLabel>

        <FloatLabel>
            <InputText v-model="filters.client_code" id="filterClientCode" class="w-full" @keyup.enter="search"/>
            <label for="filterClientCode">Код клиента</label>
        </FloatLabel>

        <FloatLabel>
            <InputText v-model="filters.client_name" id="filterClientName" class="w-full" @keyup.enter="search"/>
            <label for="filterClientName">ФИО клиента (частично)</label>
        </FloatLabel>

        <FloatLabel>
            <Dropdown v-model="filters.status_id" :options="statusOptions" optionLabel="label" optionValue="value" showClear inputId="filterStatus" class="w-full"/>
            <label for="filterStatus">Статус</label>
        </FloatLabel>

        <FloatLabel>
            <Calendar v-model="sendDateRange" selectionMode="range" dateFormat="dd.mm.yy" :manualInput="false" showIcon inputId="filterSendDate" class="w-full"/>
            <label for="filterSendDate">Дата отправки, период</label>
        </FloatLabel>
    </div>

    <div class="flex gap-2 mb-3">
        <Button label="Искать" icon="pi pi-search" :loading="loading" @click="search"/>
        <Button label="Сбросить" severity="secondary" outlined @click="reset"/>
    </div>

    <DataTable
        :value="rows"
        :loading="loading"
        dataKey="id"
        lazy
        paginator
        :rows="50"
        :totalRecords="total"
        :first="first"
        @page="onPage"
    >
        <Column field="code" header="Код"/>
        <Column field="status" header="Статус"/>
        <Column header="Сумма">
            <template #body="{data}">{{ data.cost.toFixed(2) }}</template>
        </Column>
        <Column field="send_date" header="Отправлен"/>
        <Column field="client_code" header="Код клиента"/>
        <Column field="client_name" header="Клиент"/>
        <Column header="">
            <template #body="{data}">
                <router-link :to="{name: 'containerShow', params: {id: data.id}}" v-tooltip.left="'Открыть контейнер'">
                    <span class="pi pi-external-link"></span>
                </router-link>
            </template>
        </Column>
    </DataTable>
</template>

<script setup>
import {onMounted, reactive, ref} from 'vue'
import Toolbar from 'primevue/toolbar'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import FloatLabel from 'primevue/floatlabel'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {showError} from 'app/toast'
import {containerAPI} from 'cmp/container'

const statusOptions = [
    {value: -1, label: 'Temporary'},
    {value: 1, label: 'Stopped'},
    {value: 2, label: 'InProgress'},
    {value: 3, label: 'Error'},
    {value: 4, label: 'Canceled'},
    {value: 45, label: 'Ready'},
    {value: 50, label: 'Assembling'},
    {value: 70, label: 'Sent'},
];

const blankFilters = {container_code: '', client_id: null, client_code: '', client_name: '', status_id: null};
const filters = reactive({...blankFilters});
const sendDateRange = ref(null);

const rows = ref([]);
const total = ref(0);
const page = ref(1);
const first = ref(0);
const loading = ref(false);

const formatDate = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');

    return `${y}-${m}-${d}`;
};

const buildParams = () => ({
    ...filters,
    send_date_from: sendDateRange.value?.[0] ? formatDate(sendDateRange.value[0]) : null,
    send_date_to: sendDateRange.value?.[1] ? formatDate(sendDateRange.value[1]) : null,
});

const load = () => {
    loading.value = true;

    containerAPI.search(buildParams(), page.value)
        .then((response) => {
            rows.value = response.data.data;
            total.value = response.data.total;
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось загрузить список контейнеров.'))
        .finally(() => loading.value = false);
};

onMounted(load);

const search = () => {
    page.value = 1;
    first.value = 0;
    load();
};

const reset = () => {
    Object.assign(filters, blankFilters);
    sendDateRange.value = null;
    search();
};

const onPage = (event) => {
    page.value = event.page + 1;
    first.value = event.first;
    load();
};
</script>

<style scoped>
.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
    gap: 1.5rem;
}
</style>
