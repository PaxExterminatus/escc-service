<template>
    <Toolbar>
        <template #start>
            <Button label="Search" severity="secondary" outlined @click="search"/>
        </template>
    </Toolbar>

    <ProfileCard :client="profile" @search="search"/>

    <template v-if="profile.id">
        <Panel toggleable :collapsed="messagesCollapsed">
            <template #header>
                <h1 @click="toggleMessages" class="cursor-pointer">Отправка сообщений</h1>
            </template>
        </Panel>

        <Panel toggleable :collapsed="financeCollapsed">
            <template #header>
                <h1 @click="toggleFinance" class="cursor-pointer">Финансовая информация</h1>
            </template>

            <FinanceHistoryTable :history="financeHistory" :loading="financeLoading"/>
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
import {FinanceHistoryTable, FinanceHistory} from 'cmp/finance'

const router = useRouter();
const route = useRoute();

let messagesCollapsed = ref(true);
let financeCollapsed = ref(true);
let financeLoading = ref(false);

/** @type {Profile} */
const profile = ref(Profile.empty({id: route.params.id})).value;

/** @type {FinanceHistory} */
const financeHistory = ref(new FinanceHistory).value;

onMounted(() => {
    if (profile.id) search();
});

const toggleMessages = () => {
    messagesCollapsed.value = !messagesCollapsed.value;
};

const toggleFinance = () => {
    financeCollapsed.value = !financeCollapsed.value;
};

const search = () =>
{
    profile.api.get(profile.id)
        .then((response) =>
        {
            profile.fill(response.data.profile)
            if (profile.id) router.push({ name: 'clientsProfile', params: {id: profile.id}})
        });

    financeLoading.value = true;
    financeHistory.api.get(profile.id)
        .then((response) =>
        {
            financeHistory.fill(response.data.data)
        })
        .finally(() =>
        {
            financeLoading.value = false;
        });
};
</script>
