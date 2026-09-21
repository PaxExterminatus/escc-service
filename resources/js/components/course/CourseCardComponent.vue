<template>
    <Card class="w-full">
        <template #title></template>
        <template #content>
            <InputGroup>
                <Input v-model="course().id" @enter="search" id="courseId" label="ID"/>
                <router-link
                    v-if="course().client_id"
                    :to="{name: 'clientsProfile', params: {id: course().client_id}}"
                    v-tooltip.left="'Открыть клиента'"
                    class="p-button p-component p-button-outlined"
                >
                    <span class="pi pi-user"></span>
                </router-link>
            </InputGroup>

            <InputGroup>
                <Input v-model="course().course_name" id="courseName" label="Курс"/>
                <Input v-model="course().status" id="courseStatus" label="Статус"/>
            </InputGroup>

            <InputGroup>
                <Input v-model="course().start_date" id="courseStartDate" label="Дата начала"/>
                <Input v-model="course().next_send_date" id="courseNextSendDate" label="Следующая отправка"/>
            </InputGroup>
        </template>
    </Card>
</template>

<script setup>
import InputGroup from 'primevue/inputgroup'
import Card from 'primevue/card'
import {Input} from 'element'
import {Course} from './Course.js'
import {defineEmits, defineProps} from 'vue'

const props = defineProps({
    course: Course,
});

/** @return {Course} */
const course = () => {
    return props.course;
}

const emit = defineEmits({
    search: null,
});

const search = () => {
    emit('search');
};
</script>
