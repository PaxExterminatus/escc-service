<template>
    <Toolbar>
        <template #start>
            <Button label="Search" severity="secondary" outlined @click="search"/>
        </template>
    </Toolbar>

    <ContainerCard :container="container" @search="search" @stop="stop" @start="start" @set-status="setStatus"/>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import {useRouter, useRoute} from 'vue-router'

import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import {ContainerCard, Container} from 'cmp/container'

const router = useRouter();
const route = useRoute();

/** @type {Container} */
const container = ref(Container.empty({id: route.params.id})).value;

onMounted(() => {
    if (container.id) search();
});

const search = () =>
{
    container.api.get(container.id)
        .then((response) =>
        {
            container.fill(response.data.container)
            if (container.id) router.push({ name: 'containerShow', params: {id: container.id}})
        });
};

const stop = () =>
{
    container.api.stop(container.id)
        .then((response) =>
        {
            container.fill(response.data.container)
        });
};

const start = () =>
{
    container.api.start(container.id)
        .then((response) =>
        {
            container.fill(response.data.container)
        });
};

const setStatus = (statusId) =>
{
    container.api.setStatus(container.id, statusId)
        .then((response) =>
        {
            container.fill(response.data.container)
        });
};
</script>
