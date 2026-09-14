<template>
    <Card class="w-full">
        <template #title></template>
        <template #content>
            <InputGroup>
                <Input v-model="container().id" @enter="search" id="containerId" label="ID"/>
                <SplitButton :label="container().status ?? 'Статус'" :model="statusActions" outlined/>
            </InputGroup>

            <InputGroup>
                <Input v-model="container().client_id" id="containerClientId" label="Client ID"/>
                <Input v-model="container().code" id="containerCode" label="Код"/>
                <Input v-model="container().created_at" id="containerCreatedAt" label="Дата создания"/>
            </InputGroup>

            <div v-if="container().status === 'Sent'" class="mt-3">
                <a
                    :href="invoiceUrl"
                    target="_blank"
                    class="p-button p-button-outlined"
                    v-tooltip.top="'Счёт выставляется в момент отправки контейнера'"
                >
                    <span class="pi pi-file-pdf mr-2"></span>Распечатать счёт
                </a>
            </div>
        </template>
    </Card>
</template>

<script setup>
import {computed, defineEmits, defineProps} from 'vue'
import Card from 'primevue/card'
import InputGroup from 'primevue/inputgroup'
import SplitButton from 'primevue/splitbutton'
import Input from 'element/Input.vue'
import {Container} from './Container.js'
import {invoiceAPI} from 'cmp/invoice'

const props = defineProps({
    container: Container,
});

/** @return {Container} */
const container = () => {
    return props.container;
}

const emit = defineEmits({
    search: null,
    stop: null,
    start: null,
    setStatus: null,
});

const search = () => {
    emit('search');
};

const invoiceUrl = computed(() => invoiceAPI.containerInvoiceUrl(container().id));

// Полный список статусов — App\Domain\App\Container\Enums\ContainerStatusEnum (id из REV_CONST.boxStatus*).
// Stopped/InProgress — через отдельные "stop"/"start" (там же простая семантика "остановить/запустить"),
// остальные — через общий "setStatus" с конкретным id.
const statusActions = [
    {
        label: 'Остановить [1]',
        command: () => emit('stop'),
    },
    {
        label: 'Запустить [2]',
        command: () => emit('start'),
    },
    {
        label: 'Error [3]',
        command: () => emit('setStatus', 3),
    },
    {
        label: 'Canceled [4]',
        command: () => emit('setStatus', 4),
    },
    {
        label: 'Assembling [50]',
        command: () => emit('setStatus', 50),
    },
    {
        label: 'Ready [45]',
        command: () => emit('setStatus', 45),
    },
    {
        label: 'Sent [70]',
        command: () => emit('setStatus', 70),
    },
    {
        label: 'Temporary [-1]',
        command: () => emit('setStatus', -1),
    },
];
</script>
