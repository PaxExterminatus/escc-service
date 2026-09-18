<template>
    <LoadingBlock v-if="loading" small/>
    <div v-else-if="!courses.length" class="text-color-secondary text-sm p-2">
        У клиента нет курсов.
    </div>
    <DataTable v-else :value="courses" dataKey="sub_id" selectionMode="single" :selection="selected" @update:selection="onSelect">
        <Column field="course_name" header="Курс"/>
        <Column field="next_send_date" header="Следующая отправка"/>
    </DataTable>
</template>

<script setup>
import {defineEmits, defineExpose, defineProps, onMounted, ref, watch} from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import {LoadingBlock} from 'element'
import {showError} from 'app/toast'
import {courseAPI} from './CourseAPI.js'

const props = defineProps({
    clientId: [String, Number],
    selectedSubId: {type: [String, Number], default: null},
});

const emit = defineEmits({
    select: null,
});

const courses = ref([]);
const loading = ref(false);

const selected = ref(null);

watch(() => props.selectedSubId, (subId) => {
    selected.value = courses.value.find((c) => c.sub_id === subId) ?? null;
});

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

// Кнопка "Обновить принудительно" на панели профиля вызывает это через ClientCoursesPanel
defineExpose({reload: load});

const onSelect = (course) => {
    selected.value = course;
    emit('select', course);
};
</script>
