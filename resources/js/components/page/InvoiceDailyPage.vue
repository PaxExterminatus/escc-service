<template>
    <div class="flex align-items-center justify-content-between mb-3">
        <h1 class="m-0">Печать счетов</h1>
        <Button
            icon="pi pi-refresh"
            text
            rounded
            v-tooltip.left="'Обновить'"
            :loading="loading"
            @click="load"
        />
    </div>

    <Toolbar>
        <template #start>
            <SelectButton
                v-model="mode"
                :options="modeOptions"
                optionLabel="label"
                optionValue="value"
                :allowEmpty="false"
                class="mr-2"
                @update:model-value="load"
            />
            <Button icon="pi pi-angle-left" severity="secondary" outlined class="mr-2" v-tooltip.left="stepLabel + ' назад'" @click="shift(-1)"/>
            <Button icon="pi pi-angle-right" severity="secondary" outlined class="mr-2" v-tooltip.left="stepLabel + ' вперёд'" @click="shift(1)"/>
            <Calendar v-model="date" dateFormat="dd.mm.yy" showIcon @update:model-value="load"/>
        </template>
        <template #end>
            <a
                :href="printUrl"
                target="_blank"
                class="p-button"
                :class="{'p-disabled': !containers.length}"
                v-tooltip.left="'Печатает все счета, что в списке'"
            >
                <span class="pi pi-print mr-2"></span>Печать
            </a>
        </template>
    </Toolbar>

    <div class="text-color-secondary mb-2">{{ rangeLabel }}</div>

    <DataTable :value="containers" :loading="loading" dataKey="container_id">
        <Column field="container_id" header="Контейнер"/>
        <Column field="client_code" header="Код клиента"/>
        <Column field="client_name" header="Клиент"/>
        <Column header="Отправлен">
            <template #body="{data}">{{ formatDate(data.send_date) }}</template>
        </Column>
        <Column field="container_cost" header="Сумма">
            <template #body="{data}">{{ data.container_cost.toFixed(2) }}</template>
        </Column>
        <Column header="">
            <template #body="{data}">
                <a :href="invoiceAPI.containerInvoiceUrl(data.container_id)" target="_blank" v-tooltip.left="'Распечатать счёт'">
                    <span class="pi pi-file-pdf"></span>
                </a>
            </template>
        </Column>
    </DataTable>
</template>

<script setup>
import {computed, onMounted, ref} from 'vue'
import Toolbar from 'primevue/toolbar'
import Button from 'primevue/button'
import Calendar from 'primevue/calendar'
import SelectButton from 'primevue/selectbutton'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {showError} from 'app/toast'
import {invoiceAPI} from 'cmp/invoice'

const modeOptions = [
    {label: 'За день', value: 'day'},
    {label: 'За неделю', value: 'week'},
];

const date = ref(new Date());
const mode = ref('day');
const containers = ref([]);
const loading = ref(false);

const toIso = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

// Неделя — понедельник-воскресенье, содержащая выбранную дату.
const startOfWeek = (d) => {
    const copy = new Date(d);
    const day = copy.getDay();
    const diff = (day === 0 ? -6 : 1) - day;
    copy.setDate(copy.getDate() + diff);
    return copy;
};

const endOfWeek = (d) => {
    const start = startOfWeek(d);
    const end = new Date(start);
    end.setDate(start.getDate() + 6);
    return end;
};

const range = computed(() => {
    if (mode.value === 'week') {
        return {from: toIso(startOfWeek(date.value)), to: toIso(endOfWeek(date.value))};
    }

    return {from: toIso(date.value), to: toIso(date.value)};
});

const rangeLabel = computed(() => {
    const {from, to} = range.value;
    return from === to ? from : `${from} — ${to}`;
});

const printUrl = computed(() => invoiceAPI.rangePrintUrl(range.value.from, range.value.to));

const stepLabel = computed(() => mode.value === 'week' ? 'Неделя' : 'День');

const shift = (direction) => {
    const days = mode.value === 'week' ? 7 : 1;
    const next = new Date(date.value);
    next.setDate(next.getDate() + days * direction);
    date.value = next;
    load();
};

const formatDate = (iso) => iso ? new Date(iso).toLocaleDateString('ru-RU') : '—';

const load = () => {
    loading.value = true;

    invoiceAPI.range(range.value.from, range.value.to)
        .then((response) => {
            containers.value = response.data.containers;
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось загрузить список.');
        })
        .finally(() => {
            loading.value = false;
        });
};

onMounted(load);
</script>
