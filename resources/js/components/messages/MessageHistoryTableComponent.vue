<template>
    <LoadingBlock v-if="loading && !loaded" small/>
    <div v-else-if="!messages.length" class="text-color-secondary text-sm p-2">
        Уведомлений не было.
    </div>
    <DataTable v-else :value="messages" size="small" dataKey="id">
        <Column field="date" header="Дата"/>
        <Column field="type" header="Канал"/>
        <Column field="address" header="Адрес"/>
        <Column field="body" header="Текст">
            <template #body="{data}">
                <span v-tooltip.top="data.body" class="message-body-preview">{{ data.body }}</span>
            </template>
        </Column>
        <Column header="Статус">
            <template #body="{data}">
                <Tag :severity="statusSeverity(data.status)" :value="data.status"/>
            </template>
        </Column>
    </DataTable>
</template>

<script setup>
import {defineProps} from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import {LoadingBlock} from 'element'

defineProps({
    messages: {type: Array, default: () => []},
    loading: Boolean,
    loaded: Boolean,
});

// MessageDispatchStatusEnum::label() — 'Waiting for the daily batch' / 'Sent via daily batch' / 'Sent by operator'
const statusSeverity = (status) => status?.startsWith('Waiting') ? 'warning' : 'success';
</script>

<style scoped>
.message-body-preview {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 24rem;
}
</style>
