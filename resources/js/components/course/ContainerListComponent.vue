<template>
    <div v-if="!subId" class="text-color-secondary text-sm p-2">
        Выберите курс, чтобы увидеть его посылки.
    </div>
    <LoadingBlock v-else-if="loading" small/>
    <div v-else-if="!containers.length" class="text-color-secondary text-sm p-2">
        У этого курса нет посылок.
    </div>
    <DataTable
        v-else
        :value="containers"
        dataKey="container_id"
        :selectionMode="selectable ? 'single' : null"
        @update:selection="(container) => $emit('select', container)"
    >
        <Column field="container_code" header="Посылка"/>
        <Column field="status" header="Статус"/>
        <Column field="send_date" header="Отправлена"/>
        <Column header="Сумма">
            <template #body="{data}">{{ data.container_cost.toFixed(2) }}</template>
        </Column>
        <Column header="">
            <template #body="{data}">
                <router-link
                    :to="{name: 'containerShow', params: {id: data.container_id}}"
                    v-tooltip.left="'Открыть контейнер'"
                    @click.stop
                >
                    <span class="pi pi-external-link"></span>
                </router-link>
            </template>
        </Column>
    </DataTable>
</template>

<script setup>
import {defineEmits, defineExpose, defineProps, ref, watch} from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {LoadingBlock} from 'element'
import {showError} from 'app/toast'
import {courseAPI} from './CourseAPI.js'

const props = defineProps({
    subId: {type: [String, Number], default: null},
    selectable: {type: Boolean, default: false},
});

const emit = defineEmits({
    select: null,
});

const containers = ref([]);
const loading = ref(false);

const load = () => {
    if (!props.subId) {
        containers.value = [];
        return Promise.resolve();
    }

    loading.value = true;

    return courseAPI.containers(props.subId)
        .then((response) => {
            containers.value = response.data.containers;

            // Курс почти всегда даёт ровно одну посылку — тогда клика по курсу уже достаточно
            // для вставки тега, не заставляя ещё раз выбирать из списка на одну строку.
            if (props.selectable && containers.value.length === 1) {
                emit('select', containers.value[0]);
            }
        })
        .catch(() => showError('Не удалось загрузить посылки курса.'))
        .finally(() => {
            loading.value = false;
        });
};

watch(() => props.subId, load, {immediate: true});

defineExpose({reload: load});
</script>
