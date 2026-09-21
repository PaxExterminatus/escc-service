<template>
    <LoadingBlock v-if="loading" small/>
    <div v-else-if="!courses.length" class="text-color-secondary text-sm p-2">
        У клиента нет курсов.
    </div>
    <DataTable v-else :value="courses" dataKey="sub_id" @row-click="open">
        <Column field="course_name" header="Курс" class="cursor-pointer"/>
        <Column field="next_send_date" header="Следующая отправка"/>
        <Column header="Начислено">
            <template #body="{data}">{{ data.invoiced.toFixed(2) }}</template>
        </Column>
        <Column header="Оплачено">
            <template #body="{data}">{{ data.received.toFixed(2) }}</template>
        </Column>
        <Column header="Возврат / потери">
            <template #body="{data}">
                <span v-tooltip.top="data.lost > 0 ? `Возврат: ${data.returned.toFixed(2)}, потери: ${data.lost.toFixed(2)}` : null">
                    {{ (data.returned + data.lost).toFixed(2) }}
                </span>
            </template>
        </Column>
        <Column header="Баланс">
            <template #body="{data}">
                <span :class="data.balance > 0 ? 'text-red-400' : 'text-green-400'">{{ data.balance.toFixed(2) }}</span>
            </template>
        </Column>
        <Column header="">
            <template #body="{data}">
                <router-link :to="{name: 'courseShow', params: {id: data.sub_id}}" v-tooltip.left="'Открыть курс'" @click.stop>
                    <span class="pi pi-external-link"></span>
                </router-link>
            </template>
        </Column>
    </DataTable>
</template>

<script setup>
/**
 * Курсы клиента — плоский список, без разворачивания посылок внутри (посылки, включая
 * ещё не отправленные по графику, смотрят на самой странице курса, см. CoursePage.vue).
 * Клик по строке — переход на страницу курса, а не раскрытие.
 */
import {defineExpose, defineProps, onMounted, ref, watch} from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {useRouter} from 'vue-router'
import {LoadingBlock} from 'element'
import {showError} from 'app/toast'
import {courseAPI} from './CourseAPI.js'

const props = defineProps({
    clientId: [String, Number],
});

const router = useRouter();

const courses = ref([]);
const loading = ref(false);

const load = () => {
    if (!props.clientId) return Promise.resolve();

    loading.value = true;

    return courseAPI.courses(props.clientId)
        .then((response) => {
            courses.value = response.data.courses;
        })
        .catch(() => showError('Не удалось загрузить курсы клиента.'))
        .finally(() => {
            loading.value = false;
        });
};

watch(() => props.clientId, load);
onMounted(load);

const open = ({data}) => router.push({name: 'courseShow', params: {id: data.sub_id}});

defineExpose({
    reload: load,
});
</script>
