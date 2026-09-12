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
            <template #icons>
                <button
                    type="button"
                    class="p-link p-panel-header-icon p-panel-toggler"
                    :disabled="messagesLoading"
                    v-tooltip.top="'Обновить принудительно'"
                    @click="loadMessages"
                >
                    <span :class="messagesLoading ? 'pi pi-spinner pi-spin' : 'pi pi-refresh'"></span>
                </button>
            </template>

            <MessagesPanel
                :messaging="messaging"
                :client-id="profile.id"
                :loading="messagesLoading"
                :loaded="messagesLoaded"
            />
        </Panel>

        <Panel toggleable :collapsed="financeCollapsed">
            <template #header>
                <h1 @click="toggleFinance" class="cursor-pointer">Финансовая информация</h1>
            </template>
            <template #icons>
                <button
                    type="button"
                    class="p-link p-panel-header-icon p-panel-toggler"
                    :disabled="financeLoading"
                    v-tooltip.top="'Обновить принудительно'"
                    @click="loadFinance"
                >
                    <span :class="financeLoading ? 'pi pi-spinner pi-spin' : 'pi pi-refresh'"></span>
                </button>
            </template>

            <FinanceHistoryTable :history="financeHistory" :loading="financeLoading" :loaded="financeLoaded"/>
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
import {MessagesPanel, Messaging} from 'cmp/messages'

const router = useRouter();
const route = useRoute();

let messagesCollapsed = ref(true);
let messagesLoading = ref(false);
let messagesLoaded = ref(false);
let financeCollapsed = ref(true);
let financeLoading = ref(false);
let financeLoaded = ref(false);

/** @type {Profile} */
const profile = ref(Profile.empty({id: route.params.id})).value;

/** @type {FinanceHistory} */
const financeHistory = ref(new FinanceHistory).value;

/** @type {Messaging} */
const messaging = ref(new Messaging).value;

onMounted(() => {
    if (profile.id) search();
});

const toggleMessages = () => {
    messagesCollapsed.value = !messagesCollapsed.value;

    if (!messagesCollapsed.value && !messagesLoaded.value) {
        loadMessages();
    }
};

const loadMessages = () => {
    messagesLoading.value = true;

    Promise.all([
        messaging.api.templates(true),
        messaging.api.recipient(profile.id),
    ])
        .then(([templatesResponse, recipientResponse]) => {
            messaging.fill(templatesResponse.data.data, recipientResponse.data);
            messagesLoaded.value = true;
        })
        .finally(() => {
            messagesLoading.value = false;
        });
};

const toggleFinance = () => {
    financeCollapsed.value = !financeCollapsed.value;

    // Разворачивание только подгружает, если ещё не грузили — при сворачивании данные
    // не выбрасываем (Panel скрывает контент через v-show, а не размонтирует). Обновить
    // принудительно — отдельная кнопка (см. #icons) с собственным индикатором.
    if (!financeCollapsed.value && !financeLoaded.value) {
        loadFinance();
    }
};

const search = () =>
{
    profile.api.get(profile.id)
        .then((response) =>
        {
            profile.fill(response.data.profile)
            if (profile.id) router.push({ name: 'clientsProfile', params: {id: profile.id}})
        });
};

const loadFinance = () =>
{
    financeLoading.value = true;
    financeHistory.api.get(profile.id)
        .then((response) =>
        {
            financeHistory.fill(response.data.data)
            financeLoaded.value = true;
        })
        .finally(() =>
        {
            financeLoading.value = false;
        });
};
</script>
