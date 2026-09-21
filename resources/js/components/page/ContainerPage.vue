<template>
    <div ref="contentRef">
        <Toolbar>
            <template #start>
                <Button label="Search" severity="secondary" outlined @click="search"/>
            </template>
        </Toolbar>

        <ContainerCard :container="container" @search="search" @stop="stop" @start="start" @set-status="setStatus"/>

        <template v-if="container.id">
            <div class="flex flex-column gap-3 mt-3">
                <LazyPanel
                    v-if="container.status === 'Sent'"
                    title="Счёт контейнера"
                    icon="pi pi-file-pdf"
                    v-model:collapsed="invoiceCollapsed"
                    :reloadable="false"
                >
                    <ContainerInvoicePanel :container="container"/>
                </LazyPanel>

                <LazyPanel
                    title="Финансовая информация"
                    icon="pi pi-wallet"
                    v-model:collapsed="financeCollapsed"
                    :loading="financeLoading"
                    @reload="loadFinance"
                    @expand="loadFinance"
                >
                    <LoadingBlock v-if="financeLoading && !financeLoaded" small/>
                    <div v-else class="flex flex-column gap-4">
                        <Fieldset legend="Баланс">
                            <div class="finance-summary">
                                <div class="finance-metric">
                                    <span class="text-color-secondary text-sm">Начислено</span>
                                    <span>{{ finance.invoiced.toFixed(2) }}</span>
                                </div>
                                <div class="finance-metric">
                                    <span class="text-color-secondary text-sm">Оплачено</span>
                                    <span>{{ finance.received.toFixed(2) }}</span>
                                </div>
                                <div class="finance-metric">
                                    <span class="text-color-secondary text-sm">Возврат / потери</span>
                                    <span v-tooltip.top="finance.lost > 0 ? `Возврат: ${finance.returned.toFixed(2)}, потери: ${finance.lost.toFixed(2)}` : null">
                                        {{ (finance.returned + finance.lost).toFixed(2) }}
                                    </span>
                                </div>
                                <div class="finance-metric">
                                    <span class="text-color-secondary text-sm">Баланс</span>
                                    <span :class="finance.balance > 0 ? 'text-red-400' : 'text-green-400'">{{ finance.balance.toFixed(2) }}</span>
                                </div>
                            </div>
                        </Fieldset>

                        <!-- CONTAINER.POST_FEE/POST_FEE_CLIENT — фактическая стоимость пересылки
                             и то, что выставлено клиенту за неё (обычно совпадает, но не всегда,
                             напр. при акциях с бесплатной доставкой). -->
                        <Fieldset legend="Почтовые расходы">
                            <div class="finance-summary">
                                <div class="finance-metric">
                                    <span class="text-color-secondary text-sm">Выставлено клиенту</span>
                                    <span>{{ finance.postage.fee_client.toFixed(2) }}</span>
                                </div>
                                <div class="finance-metric">
                                    <span class="text-color-secondary text-sm">Фактическая стоимость</span>
                                    <span>{{ finance.postage.fee.toFixed(2) }}</span>
                                </div>
                            </div>
                        </Fieldset>

                        <!-- Содержимое контейнера — строки CLIENT_BASKET, отправленные именно в
                             этой посылке (в контейнере может быть один урок или несколько —
                             CLIENT_BASKET иерархична, см. ContainerController::finance()). -->
                        <Fieldset legend="Содержимое">
                            <div v-if="!finance.items.length" class="text-color-secondary text-sm">
                                Содержимое не указано.
                            </div>
                            <ul v-else class="content-list">
                                <li v-for="(item, index) in finance.items" :key="index" class="content-item">
                                    <span>{{ item.name }}</span>
                                    <span v-if="item.discount > 0" class="text-color-secondary text-sm">(скидка {{ item.discount.toFixed(2) }})</span>
                                    <span class="ml-auto">{{ item.cost.toFixed(2) }}</span>
                                </li>
                            </ul>
                        </Fieldset>
                    </div>
                </LazyPanel>

                <LazyPanel
                    title="Уведомления"
                    icon="pi pi-bell"
                    v-model:collapsed="messagesCollapsed"
                    :loading="messagesLoading"
                    @reload="loadMessages"
                    @expand="loadMessages"
                >
                    <MessageHistoryTable :messages="messageHistory" :loading="messagesLoading" :loaded="messagesLoaded"/>
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
import Fieldset from 'primevue/fieldset'
import {ContainerCard, ContainerInvoicePanel, Container} from 'cmp/container'
import {MessageHistoryTable, messagingAPI} from 'cmp/messages'
import {AnchorMenu, LazyPanel, LoadingBlock} from 'cmp/element'

const router = useRouter();
const route = useRoute();

const contentRef = ref(null);

/** @type {Container} */
const container = ref(Container.empty({id: route.params.id})).value;

// Все вкладки страницы разворачиваются сразу — оператору не нужно кликать по каждой отдельно.
const invoiceCollapsed = ref(false);

const financeCollapsed = ref(false);
const financeLoading = ref(false);
const financeLoaded = ref(false);
const finance = ref({invoiced: 0, received: 0, returned: 0, lost: 0, balance: 0, postage: {fee: 0, fee_client: 0}, items: []});

const messagesCollapsed = ref(false);
const messagesLoading = ref(false);
const messagesLoaded = ref(false);
const messageHistory = ref([]);

onMounted(() => {
    if (container.id) {
        search().then(() => {
            loadFinance();
            loadMessages();
        });
    }
});

const search = () =>
{
    return container.api.get(container.id)
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

const loadFinance = () => {
    financeLoading.value = true;

    container.api.finance(container.id)
        .then((response) => {
            finance.value = response.data;
            financeLoaded.value = true;
        })
        .finally(() => {
            financeLoading.value = false;
        });
};

const loadMessages = () => {
    messagesLoading.value = true;

    messagingAPI.history(container.client_id)
        .then((response) => {
            messageHistory.value = response.data.messages;
            messagesLoaded.value = true;
        })
        .finally(() => {
            messagesLoading.value = false;
        });
};
</script>

<style scoped>
.finance-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
    gap: 1rem;
}

.finance-metric {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.content-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.content-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
</style>
