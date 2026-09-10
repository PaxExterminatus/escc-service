<template>
    <DataTable :value="history().items" :loading="loading" size="small" dataKey="id">
        <Column field="operation_date" header="Дата">
            <template #body="{data}">{{ formatDate(data.operation_date) }} ({{ relativeTimeAgo(data.operation_date) }})</template>
        </Column>
        <Column field="operation_type" header="Тип">
            <template #body="{data}">{{ operationTypeLabel(data.operation_type) }}</template>
        </Column>
        <Column field="amount" header="Сумма">
            <template #body="{data}">
                <span :class="amountClass(data.operation_type)">{{ signedAmount(data) }}</span>
            </template>
        </Column>
        <Column field="description" header="Описание"/>
        <Column field="course_name" header="Курс">
            <template #body="{data}">{{ data.course_name ?? '—' }}</template>
        </Column>
    </DataTable>
</template>

<script setup>
import {defineProps} from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {FinanceHistory} from './FinanceHistory.js'
import {relativeTimeAgo} from './FinanceDate.js'

const props = defineProps({
    history: FinanceHistory,
    loading: Boolean,
});

/** @return {FinanceHistory} */
const history = () => {
    return props.history;
}

const operationTypeLabels = {
    CHARGE: 'Начисление',
    PAYMENT: 'Оплата',
};

const operationTypeLabel = (type) => operationTypeLabels[type] ?? type;

const formatDate = (value) => value ? value.substring(0, 16) : '';

// CHARGE ("Начисление") — списание/долг клиента, показываем в минус и красным.
// PAYMENT ("Оплата") — поступление, показываем в плюс и зелёным.
const isCharge = (type) => type === 'CHARGE';

const amountClass = (type) => isCharge(type) ? 'text-red-400 font-semibold' : 'text-green-400 font-semibold';

const signedAmount = (row) => `${isCharge(row.operation_type) ? '-' : '+'}${row.amount}`;
</script>
