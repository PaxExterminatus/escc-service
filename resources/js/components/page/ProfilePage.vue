<template>
    <Toolbar>
        <template #start>
            <Button label="Search" severity="secondary" outlined @click="search"/>
        </template>
    </Toolbar>

    <ProfileCard :client="profile" @search="search"/>

    <template v-if="profile.id">
        <Panel toggleable :collapsed="collapsed">
            <template #header>
                <h1 @click="toggle" class="cursor-pointer">Отправка сообщений</h1>
            </template>
        </Panel>
    </template>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import {useRouter, useRoute} from 'vue-router'

import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import Panel from 'primevue/panel'
import {ProfileCard, Profile} from 'cmp/profile'

const router = useRouter();
const route = useRoute();

let collapsed = ref(true);

/** @type {Profile} */
const profile = ref(Profile.empty({id: route.params.id})).value;

onMounted(() => {
    if (profile.id) search();
});

const toggle = () => {
    collapsed.value = !collapsed.value;
};

const search = () =>
{
    profile.api.get(profile.id)
        .then(response =>
        {
            profile.fill(response.data.profile)
            if (profile.id) router.push({ name: 'clientsProfile', params: {id: profile.id}})
        });
};
</script>
