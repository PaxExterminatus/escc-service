<template>
    <div v-if="loading && !loaded" class="flex justify-content-center align-items-center p-4">
        <ProgressSpinner style="width: 40px; height: 40px" strokeWidth="4"/>
    </div>
    <div v-else class="flex flex-column gap-3 p-2">
        <SelectButton
            v-model="channel"
            :options="channelOptions"
            optionLabel="label"
            optionValue="value"
            :allowEmpty="false"
        />

        <div class="text-sm text-color-secondary">
            {{ selectedChannel?.label }}: {{ selectedChannel?.address ?? '—' }}
        </div>

        <Message v-if="loaded && selectedChannel && !selectedChannel.allowed" severity="warn" :closable="false">
            Клиент не дал согласие на этот канал либо адрес некорректен.
        </Message>

        <Dropdown
            v-model="templateId"
            :options="messaging.templates"
            optionLabel="name"
            optionValue="id"
            placeholder="Выбрать шаблон"
            showClear
            :disabled="renderingTemplate"
            @change="applyTemplate"
        />

        <Textarea v-model="body" rows="4" autoResize :disabled="renderingTemplate"/>

        <div>
            <Button label="Отправить" :disabled="!canSend" :loading="sending" @click="send"/>
        </div>
    </div>
</template>

<script setup>
import {computed, defineProps, ref, watch} from 'vue'
import ProgressSpinner from 'primevue/progressspinner'
import SelectButton from 'primevue/selectbutton'
import Dropdown from 'primevue/dropdown'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Message from 'primevue/message'
import {showError, showSuccess} from 'app/toast'
import {Messaging} from './Messaging.js'

const props = defineProps({
    messaging: Messaging,
    clientId: [String, Number],
    loading: Boolean,
    loaded: Boolean,
});

const channel = ref(null);
const templateId = ref(null);
const body = ref('');
const sending = ref(false);
const renderingTemplate = ref(false);

const channelOptions = computed(() => props.messaging.channels.map((c) => ({label: c.label, value: c.code})));
const selectedChannel = computed(() => props.messaging.channel(channel.value));

watch(() => props.loaded, (loaded) => {
    if (loaded && !channel.value) {
        channel.value = props.messaging.channels[0]?.code ?? null;
    }
}, {immediate: true});

const canSend = computed(() => props.messaging.isChannelAllowed(channel.value) && body.value.trim().length > 0 && !sending.value);

const applyTemplate = () => {
    const template = props.messaging.templates.find((t) => t.id === templateId.value);

    if (!template) {
        body.value = '';
        return;
    }

    renderingTemplate.value = true;

    props.messaging.api.renderTemplate(template.id, props.clientId)
        .then((response) => {
            body.value = response.data.body;
        })
        .catch(() => {
            body.value = template.body;
            showError('Не удалось собрать шаблон с данными клиента, вставлен исходный текст.');
        })
        .finally(() => {
            renderingTemplate.value = false;
        });
};

const send = () => {
    sending.value = true;

    props.messaging.api.send({
        client_id: props.clientId,
        channel: channel.value,
        template_id: templateId.value,
        body: body.value,
    })
        .then(() => {
            showSuccess(`${selectedChannel.value?.label ?? 'Сообщение'} отправлено.`);
            body.value = '';
            templateId.value = null;
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось отправить сообщение.');
        })
        .finally(() => {
            sending.value = false;
        });
};
</script>
