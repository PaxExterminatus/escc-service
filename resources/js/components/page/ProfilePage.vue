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
                <FloatLabel>
                    <InputText v-model="client.id" inputId="clientId" :useGrouping="false" @keydown.enter="search"/>
                    <label for="clientId">ID</label>
                </FloatLabel>
            </InputGroup>

            <InputGroup>
                <FloatLabel>
                    <InputText v-model="client.name" inputId="clientName" :useGrouping="false"/>
                    <label for="clientName">Имя</label>
                </FloatLabel>

                <FloatLabel>
                    <InputText v-model="client.name_middle" inputId="clientNameMiddle" :useGrouping="false"/>
                    <label for="clientNameMiddle">Отчество</label>
                </FloatLabel>

                <FloatLabel>
                    <InputText v-model="client.name_last" inputId="clientNameLast" :useGrouping="false"/>
                    <label for="clientNameLast">Фамилия</label>
                </FloatLabel>
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
import InputText from 'primevue/inputtext'
import InputGroup from 'element/InputGroup'
import FloatLabel from 'primevue/floatlabel'
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
