<template>
    <div ref="contentRef">
        <Toolbar>
            <template #start>
                <Button label="Search" severity="secondary" outlined @click="search"/>
            </template>
        </Toolbar>

        <ProfileCard :client="profile" @search="search" @contacts-updated="onContactsUpdated"/>

        <template v-if="profile.id">
            <div class="flex flex-column gap-3 mt-3">
                <!-- Курсы — самая частая причина открыть профиль, поэтому раскрыта сразу -->
                <LazyPanel
                    title="Курсы"
                    icon="pi pi-book"
                    v-model:collapsed="coursesCollapsed"
                    :loading="coursesLoading"
                    @reload="loadCourses"
                >
                    <ClientCoursesPanel ref="coursesPanelRef" :client-id="profile.id"/>
                </LazyPanel>

                <LazyPanel
                    title="Отправка сообщений"
                    icon="pi pi-send"
                    v-model:collapsed="messagesCollapsed"
                    :loading="messagesLoading"
                    @reload="loadMessages"
                    @expand="loadMessages"
                >
                    <MessagesPanel
                        :messaging="messaging"
                        :client-id="profile.id"
                        :loading="messagesLoading"
                        :loaded="messagesLoaded"
                    />
                </LazyPanel>

                <LazyPanel
                    title="Финансовая информация"
                    icon="pi pi-wallet"
                    v-model:collapsed="financeCollapsed"
                    :loading="financeLoading"
                    @reload="loadFinance"
                    @expand="loadFinance"
                >
                    <FinanceHistoryTable :history="financeHistory" :loading="financeLoading" :loaded="financeLoaded"/>
                </LazyPanel>
            </div>
        </template>
    </div>

    <AnchorMenu :container="contentRef"/>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import {useRouter, useRoute} from 'vue-router'

import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import {ProfileCard, Profile} from 'cmp/profile'
import {FinanceHistoryTable, FinanceHistory} from 'cmp/finance'
import {MessagesPanel, Messaging} from 'cmp/messages'
import {ClientCoursesPanel} from 'cmp/course'
import {templateAPI} from 'cmp/templates'
import {AnchorMenu, LazyPanel} from 'cmp/element'

const router = useRouter();
const route = useRoute();

const contentRef = ref(null);
const coursesPanelRef = ref(null);

let messagesCollapsed = ref(true);
let messagesLoading = ref(false);
let messagesLoaded = ref(false);
let financeCollapsed = ref(true);
let financeLoading = ref(false);
let financeLoaded = ref(false);
let coursesCollapsed = ref(false);
let coursesLoading = ref(false);

/** @type {Profile} */
const profile = ref(Profile.empty({id: route.params.id})).value;

/** @type {FinanceHistory} */
const financeHistory = ref(new FinanceHistory).value;

/** @type {Messaging} */
const messaging = ref(new Messaging).value;

onMounted(() => {
    if (profile.id) search();
});

const loadMessages = () => {
    messagesLoading.value = true;

    Promise.all([
        templateAPI.index(true),
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

// Контакты (телефон/email/согласия) влияют на доступные каналы отправки — если панель
// сообщений уже подгружалась, обновляем её данные о получателе, чтобы не показывать устаревшие.
const onContactsUpdated = () => {
    if (messagesLoaded.value) {
        loadMessages();
    }
};

const loadCourses = () => {
    coursesLoading.value = true;

    coursesPanelRef.value?.reload().finally(() => {
        coursesLoading.value = false;
    });
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
