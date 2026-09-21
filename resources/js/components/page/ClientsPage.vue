<template>
    <Toolbar>
        <template #start>
            <h1>Поиск клиентов</h1>
        </template>
    </Toolbar>

    <div class="filters-grid my-3">
        <FloatLabel>
            <InputText v-model="filters.client_code" id="filterClientCode" class="w-full" @keyup.enter="search"/>
            <label for="filterClientCode">Код клиента</label>
        </FloatLabel>

        <FloatLabel>
            <InputText v-model="filters.name" id="filterName" class="w-full" @keyup.enter="search"/>
            <label for="filterName">ФИО (частично)</label>
        </FloatLabel>

        <FloatLabel>
            <InputText v-model="filters.phone" id="filterPhone" class="w-full" @keyup.enter="search"/>
            <label for="filterPhone">Телефон</label>
        </FloatLabel>

        <FloatLabel>
            <InputText v-model="filters.email" id="filterEmail" class="w-full" @keyup.enter="search"/>
            <label for="filterEmail">Email</label>
        </FloatLabel>

        <FloatLabel>
            <Dropdown v-model="filters.sex" :options="sexOptions" optionLabel="name" optionValue="code" showClear inputId="filterSex" class="w-full"/>
            <label for="filterSex">Пол</label>
        </FloatLabel>

        <FloatLabel>
            <Calendar v-model="birthdayRange" selectionMode="range" dateFormat="dd.mm.yy" :manualInput="false" showIcon inputId="filterBirthday" class="w-full"/>
            <label for="filterBirthday">Дата рождения, период</label>
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
        <Column field="name" header="ФИО"/>
        <Column field="birthday" header="Дата рождения"/>
        <Column field="phone" header="Телефон"/>
        <Column field="email" header="Email"/>
        <Column header="">
            <template #body="{data}">
                <router-link :to="{name: 'clientsProfile', params: {id: data.id}}" v-tooltip.left="'Открыть профиль'">
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
import {profileAPI, profileSexOptions} from 'cmp/profile'

const sexOptions = profileSexOptions.filter((o) => o.code !== 'unknown');

const blankFilters = {client_code: '', name: '', phone: '', email: ''};
const filters = reactive({...blankFilters, sex: null});
const birthdayRange = ref(null);

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
    birthday_from: birthdayRange.value?.[0] ? formatDate(birthdayRange.value[0]) : null,
    birthday_to: birthdayRange.value?.[1] ? formatDate(birthdayRange.value[1]) : null,
});

const load = () => {
    loading.value = true;

    profileAPI.search(buildParams(), page.value)
        .then((response) => {
            rows.value = response.data.data;
            total.value = response.data.total;
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось загрузить список клиентов.'))
        .finally(() => loading.value = false);
};

onMounted(load);

const search = () => {
    page.value = 1;
    first.value = 0;
    load();
};

const reset = () => {
    Object.assign(filters, blankFilters, {sex: null});
    birthdayRange.value = null;
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
