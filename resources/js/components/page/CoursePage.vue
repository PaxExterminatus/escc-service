<template>
    <Toolbar>
        <template #start>
            <Button label="Search" severity="secondary" outlined @click="search"/>
        </template>
    </Toolbar>

    <CourseCard :course="course" @search="search"/>

    <ShipmentScheduleTimeline v-if="course.id" :items="timelineItems" class="mt-3" @select="openContainer"/>

    <DataTable
        v-if="course.id"
        :value="containers"
        :loading="containersLoading"
        dataKey="_key"
        class="mt-3"
        :rowClass="rowClass"
    >
        <Column header="Посылка">
            <template #body="{data}">
                <span v-if="data.virtual" class="text-color-secondary">План{{ data.units ? `, ${data.units} ур.` : '' }}</span>
                <span v-else>{{ data.container_code }}</span>
            </template>
        </Column>
        <Column field="status" header="Статус"/>
        <Column field="send_date" header="Отправлена"/>
        <Column header="Сумма">
            <template #body="{data}">
                <span v-if="data.container_cost === null">—</span>
                <span v-else><span v-if="data.cost_estimated">≈</span>{{ data.container_cost.toFixed(2) }}</span>
            </template>
        </Column>
        <Column header="">
            <template #body="{data}">
                <router-link
                    v-if="!data.virtual"
                    :to="{name: 'containerShow', params: {id: data.container_id}}"
                    v-tooltip.left="'Открыть контейнер'"
                >
                    <span class="pi pi-external-link"></span>
                </router-link>
            </template>
        </Column>
    </DataTable>
</template>

<script setup>
import {computed, onMounted, ref} from 'vue'
import {useRouter, useRoute} from 'vue-router'

import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {CourseCard, Course, ShipmentScheduleTimeline} from 'cmp/course'
import {showError} from 'app/toast'

const router = useRouter();
const route = useRoute();

/** @type {Course} */
const course = ref(Course.empty({id: route.params.id})).value;

const containers = ref([]);
const containersLoading = ref(false);

onMounted(() => {
    if (course.id) search();
});

const search = () => {
    course.api.get(course.id)
        .then((response) => {
            course.fill(response.data.course);
            if (course.id) router.push({name: 'courseShow', params: {id: course.id}});

            loadContainers();
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось найти курс.'));
};

const loadContainers = () => {
    containersLoading.value = true;

    course.api.containers(course.id)
        .then((response) => {
            // У плановых строк (см. CourseScheduleCalculator) container_id всегда null —
            // dataKey нужен свой, иначе несколько строк претендуют на один и тот же ключ.
            containers.value = response.data.containers.map((data, index) => ({
                ...data,
                _key: data.container_id ?? `planned-${index}`,
            }));
        })
        .catch(() => showError('Не удалось загрузить посылки курса.'))
        .finally(() => {
            containersLoading.value = false;
        });
};

const rowClass = (data) => data.virtual ? 'course-row-planned' : null;

const openContainer = (item) => router.push({name: 'containerShow', params: {id: item.container_id}});

// Таблица показывает реальные посылки от новых к старым (удобно читать список), а лента —
// строго по датам слева-направо (прошлое → будущее), иначе линия связей теряет смысл.
const parseDate = (value) => {
    const [day, month, year] = value.split('.');
    return `${year}-${month}-${day}`;
};

const timelineItems = computed(() => [...containers.value].sort((a, b) => parseDate(a.send_date).localeCompare(parseDate(b.send_date))));
</script>

<style>
/* Плановые строки графика отправки (см. CourseScheduleCalculator) — контейнера под ними ещё
   нет, поэтому визуально приглушены и без сплошной нижней границы. Не scoped: :deep() не
   применяется к динамическому rowClass PrimeVue надёжно во всех версиях. */
.course-row-planned {
    opacity: 0.6;
}

.course-row-planned td {
    border-bottom-style: dashed !important;
}
</style>
