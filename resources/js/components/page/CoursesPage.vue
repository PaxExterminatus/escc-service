<template>
    <Toolbar>
        <template #start>
            <h1>Поиск курсов</h1>
        </template>
    </Toolbar>

    <div class="filters-grid my-3">
        <FloatLabel>
            <InputText v-model="filters.sub_code" id="filterSubCode" class="w-full" @keyup.enter="search"/>
            <label for="filterSubCode">Код подписки</label>
        </FloatLabel>

        <FloatLabel>
            <InputText v-model.number="filters.client_id" id="filterClientId" class="w-full" @keyup.enter="search"/>
            <label for="filterClientId">ID клиента</label>
        </FloatLabel>

        <FloatLabel>
            <InputText v-model="filters.client_name" id="filterClientName" class="w-full" @keyup.enter="search"/>
            <label for="filterClientName">ФИО клиента (частично)</label>
        </FloatLabel>

        <FloatLabel>
            <InputText v-model="filters.course_name" id="filterCourseName" class="w-full" @keyup.enter="search"/>
            <label for="filterCourseName">Название курса (частично)</label>
        </FloatLabel>

        <FloatLabel>
            <Calendar v-model="startDateRange" selectionMode="range" dateFormat="dd.mm.yy" :manualInput="false" showIcon inputId="filterStartDate" class="w-full"/>
            <label for="filterStartDate">Дата начала, период</label>
        </FloatLabel>
    </div>

    <div class="flex gap-2 mb-3">
        <Button label="Искать" icon="pi pi-search" :loading="loading" @click="search"/>
        <Button label="Сбросить" severity="secondary" outlined @click="reset"/>
    </div>

    <DataTable
        :value="rows"
        :loading="loading"
        dataKey="sub_id"
        lazy
        paginator
        :rows="50"
        :totalRecords="total"
        :first="first"
        @page="onPage"
    >
        <Column field="course_name" header="Курс"/>
        <Column field="status" header="Статус"/>
        <Column field="start_date" header="Начало"/>
        <Column field="next_send_date" header="Следующая отправка"/>
        <Column field="client_code" header="Код клиента"/>
        <Column field="client_name" header="Клиент"/>
        <Column header="">
            <template #body="{data}">
                <router-link :to="{name: 'courseShow', params: {id: data.sub_id}}" v-tooltip.left="'Открыть курс'">
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
import Calendar from 'primevue/calendar'
import FloatLabel from 'primevue/floatlabel'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {showError} from 'app/toast'
import {courseAPI} from 'cmp/course'

const blankFilters = {sub_code: '', client_id: null, client_name: '', course_name: ''};
const filters = reactive({...blankFilters});
const startDateRange = ref(null);

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
    start_date_from: startDateRange.value?.[0] ? formatDate(startDateRange.value[0]) : null,
    start_date_to: startDateRange.value?.[1] ? formatDate(startDateRange.value[1]) : null,
});

const load = () => {
    loading.value = true;

    courseAPI.search(buildParams(), page.value)
        .then((response) => {
            rows.value = response.data.data;
            total.value = response.data.total;
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось загрузить список курсов.'))
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
    startDateRange.value = null;
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
