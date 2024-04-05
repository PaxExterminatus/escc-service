<template>
    <Toolbar>
        <template #start>
            <Button label="Search" severity="secondary" outlined @click="search"/>
        </template>
    </Toolbar>

    <ProfileCard :client="client" @search="search"/>

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
import {useRouter, useRoute} from 'vue-router'

import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import Panel from 'primevue/panel'
import ProfileCard from 'cmp/profile/ProfileCard'

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
