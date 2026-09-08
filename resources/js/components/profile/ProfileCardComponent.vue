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
                <Input v-model="profile().birthday" :label="birthdayLabel" id="clientBirthday"/>

                <FloatLabel>
                    <Dropdown v-model="profile().sex" :options="sexes" optionLabel="name" id="clientSex"/>
                    <label for="clientSex">Sex</label>
                </FloatLabel>
            </InputGroup>
        </template>
    </Card>
</template>

<script setup>
import {computed, defineEmits, defineProps, ref} from 'vue'
import Card from 'primevue/card'
import InputGroup from 'primevue/inputgroup'
import Input from 'element/Input.vue'
import {Profile} from './Profile.js'
import {profileSexOptions} from './ProfileSex.js'
import {birthdayInfo} from './BirthdayInfo.js'

import Dropdown from 'primevue/dropdown'
import FloatLabel from 'primevue/floatlabel';

const props = defineProps({
    client: Profile,
});

/** @return {Profile} */
const profile = () => {
    return props.client;
}

const sexes = ref(profileSexOptions);

const info = computed(() => birthdayInfo(profile().birthday));
const birthdayLabel = computed(() => {
    if (!info.value) return 'Birthday';

    return `Birthday (${info.value.age}), ${info.value.countdown}`;
});

const emit = defineEmits({
    search: null,
});

const search = () => {
    emit('search');
};

</script>
