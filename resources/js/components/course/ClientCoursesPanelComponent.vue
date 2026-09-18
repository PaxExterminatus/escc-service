<template>
    <div class="flex flex-column gap-3">
        <CourseList ref="courseListRef" :client-id="clientId" :selected-sub-id="selectedSubId" @select="onSelectCourse"/>
        <ContainerList ref="containerListRef" :sub-id="selectedSubId" :selectable="selectable" @select="(container) => $emit('select-container', container)"/>
    </div>
</template>

<script setup>
import {defineEmits, defineExpose, defineProps, ref} from 'vue'
import CourseList from './CourseListComponent.vue'
import ContainerList from './ContainerListComponent.vue'

defineProps({
    clientId: [String, Number],
    // В пикере тегов — контейнер выбирается кнопкой; в панели профиля выбор не нужен,
    // там переход к контейнеру идёт по иконке "Открыть" (см. ContainerListComponent).
    selectable: {type: Boolean, default: false},
});

defineEmits({
    'select-container': null,
});

const selectedSubId = ref(null);

const onSelectCourse = (course) => {
    selectedSubId.value = course.sub_id;
};

const courseListRef = ref(null);
const containerListRef = ref(null);

// Кнопка "Обновить принудительно" на панели профиля — обновляет и список курсов, и посылки
// текущего выбранного курса (если он выбран).
defineExpose({
    reload: () => Promise.all([
        courseListRef.value?.reload(),
        containerListRef.value?.reload(),
    ]),
});
</script>
