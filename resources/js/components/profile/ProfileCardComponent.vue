<template>
    <Card class="w-full">
        <template #title></template>
        <template #content>
            <InputGroup>
                <Input v-model="props.client.id" @enter="search" id="clientId" label="ID"/>
            </InputGroup>

            <InputGroup>
                <Input v-model="props.client.name_last" id="clientNameMiddle" label="Фамилия"/>
                <Input v-model="props.client.name" id="clientName" label="Имя"/>
                <Input v-model="props.client.name_middle" id="clientNameMiddle" label="Отчество"/>
            </InputGroup>

            <InputGroup>
                <Input v-model="profile.birthday" id="clientBirthday" label="Birthday"/>
            </InputGroup>
        </template>
    </Card>
</template>

<script setup>
import {defineEmits, defineProps} from 'vue'
import Card from 'primevue/card'
import InputGroup from 'element/InputGroup.vue'
import Input from 'element/Input.vue'
import {Profile} from './Profile.js'

const props = defineProps({
    client: Object,
});

/**
 * @type {Profile}
 */
const profile = props.client;

const emit = defineEmits({
    search: null,
});

const search = () => {
    emit('search');
};

</script>
