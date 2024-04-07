<template>
    <Card class="w-full">
        <template #title></template>
        <template #content>
            <InputGroup>
                <Input v-model="profile().id" @enter="search" id="clientId" label="ID"/>
            </InputGroup>

            <InputGroup>
                <Input v-model="profile().name_last" id="clientNameMiddle" label="Фамилия"/>
                <Input v-model="profile().name" id="clientName" label="Имя"/>
                <Input v-model="profile().name_middle" id="clientNameMiddle" label="Отчество"/>
            </InputGroup>

            <InputGroup>
                <Input v-model="profile().birthday" id="clientBirthday" label="Birthday"/>
            </InputGroup>
        </template>
    </Card>
</template>

<script setup>
import {defineEmits, defineProps, computed} from 'vue'
import Card from 'primevue/card'
import InputGroup from 'primevue/inputgroup'
import Input from 'element/Input.vue'
import {Profile} from './Profile.js'

const props = defineProps({
    client: Profile,
});

/**
 * @return {Profile}
 */
const profile = () => {
    return props.client;
}

const emit = defineEmits({
    search: null,
});

const search = () => {
    emit('search');
};

</script>
