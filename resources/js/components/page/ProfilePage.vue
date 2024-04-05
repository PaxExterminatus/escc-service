<template>
    <Toolbar>
        <template #start>
            <Button label="Search" severity="secondary" outlined @click="search"/>
        </template>
    </Toolbar>

    <Card class="w-full">
        <template #title></template>
        <template #content>
            <InputGroup>
                <Input v-model="client.id" @enter="search" id="clientId" label="ID"/>
            </InputGroup>

            <InputGroup>
                <Input v-model="client.name_last" id="clientNameMiddle" label="Фамилия"/>
                <Input v-model="client.name" id="clientName" label="Имя"/>
                <Input v-model="client.name_middle" id="clientNameMiddle" label="Отчество"/>
            </InputGroup>
        </template>
    </Card>

    <template v-if="client.id">
        <Panel toggleable :collapsed="collapsed">
            <template #header>
                <h1 @click="toggle" class="cursor-pointer">Отправка сообщений</h1>
            </template>
        </Panel>
    </template>
</template>

<script setup>
import axios from 'axios'
import {onMounted, ref} from 'vue'
import { useRouter, useRoute } from 'vue-router'
import Card from 'primevue/card'

import Input from 'element/Input'
import InputGroup from 'element/InputGroup'

import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import Panel from 'primevue/panel'

const router = useRouter();
const route = useRoute();

let collapsed = ref(true);

const client = ref({
    id: route.params.id ?? null,
    name: null,
    name_last: null,
    name_middle: null,
}).value;

onMounted(() => {
    if (client.id) search();
});

const toggle = () => {
    collapsed.value = !collapsed.value;
};

const search = () =>
{
    console.log('search', client.id);
    axios.get(`/api/profile/${client.id}`)
        .then((response) => {
            client.name = response.data.profile.name;
            client.name_last = response.data.profile.name_last;
            client.name_middle = response.data.profile.name_middle;

            if (client.id)
                router.push({ name: 'clientsProfile', params: { id: response.data.profile.id } })
        });
};
</script>
