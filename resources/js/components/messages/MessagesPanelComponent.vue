<template>
    <div v-if="loading && !loaded" class="flex justify-content-center align-items-center p-4">
        <ProgressSpinner style="width: 40px; height: 40px" strokeWidth="4"/>
    </div>
    <div v-else class="flex flex-column gap-3 p-2">
        <div class="flex align-items-center gap-3">
            <SelectButton
                v-model="channel"
                :options="channelOptions"
                optionLabel="label"
                optionValue="value"
                :allowEmpty="false"
            />

            <Button label="Отправить" :disabled="!canSend" :loading="sending" @click="send"/>
        </div>

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

        <SmsBodyEditor v-if="channel === 'sms'" v-model="body" id="messagePanelBody" label="Текст SMS" :disabled="renderingTemplate" :client-id="clientId"/>
        <EmailBodyEditor v-else-if="channel === 'email'" v-model="body" id="messagePanelBody" label="Текст письма" :disabled="renderingTemplate" :client-id="clientId" :template-id="templateId" @send="send"/>
    </div>
</template>

<script setup>
import {computed, defineProps, ref, watch} from 'vue'
import ProgressSpinner from 'primevue/progressspinner'
import SelectButton from 'primevue/selectbutton'
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
import Message from 'primevue/message'
import {showError, showSuccess} from 'app/toast'
import {Messaging} from './Messaging.js'
import SmsBodyEditor from './SmsBodyEditorComponent.vue'
import EmailBodyEditor from './EmailBodyEditorComponent.vue'
import {templateAPI} from 'cmp/templates'

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

    templateAPI.render(template.id, props.clientId)
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
    // Кнопка "Отправить" в предпросмотре письма вызывает эту же функцию — повторяем ту же
    // проверку, что и disabled на обычной кнопке, иначе оттуда можно отправить без согласия
    // клиента на канал или пустое тело.
    if (!canSend.value) {
        showError('Отправка недоступна: клиент не дал согласие на этот канал, либо текст пуст.');
        return;
    }

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
