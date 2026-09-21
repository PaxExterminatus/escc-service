<template>
    <Toolbar>
        <template #start>
            <h1>Массовые рассылки — очередь</h1>
        </template>
        <template #end>
            <a :href="txtUrl" target="_blank" class="p-button p-button-text mr-2">
                <span class="pi pi-download mr-2"></span>Скачать .txt
            </a>
            <Button label="Обновить" icon="pi pi-refresh" severity="secondary" outlined class="mr-2" :loading="loading" @click="load"/>
            <Button label="Отправить" icon="pi pi-send" :disabled="!messages.length" :loading="sending" @click="send"/>
        </template>
    </Toolbar>

    <div class="flex align-items-center justify-content-between my-3">
        <SelectButton v-model="type" :options="typeOptions" optionLabel="label" optionValue="value" :allowEmpty="false"/>
        <Button label="Добавить" icon="pi pi-plus" @click="openCreate"/>
    </div>

    <div class="flex align-items-center gap-2 flex-wrap mb-3">
        <SelectButton v-model="preset" :options="presetOptions" optionLabel="label" optionValue="value" :allowEmpty="false"/>
        <Calendar
            v-if="preset === 'range'"
            v-model="customRange"
            selectionMode="range"
            dateFormat="dd.mm.yy"
            :manualInput="false"
            showIcon
            placeholder="Выберите период"
        />
        <Button
            label="Все неотправленные"
            severity="secondary"
            :outlined="preset !== 'all'"
            @click="preset = 'all'"
        />
    </div>

    <DataTable :value="messages" :loading="loading" dataKey="id">
        <Column field="id" header="ID"/>
        <Column field="address" header="Адрес"/>
        <Column field="body" header="Текст"/>
        <Column header="">
            <template #body="{data}">
                <Button icon="pi pi-pencil" text v-tooltip.left="'Редактировать'" @click="openEdit(data)"/>
                <Button icon="pi pi-trash" text severity="danger" v-tooltip.left="'Удалить'" @click="remove(data)"/>
            </template>
        </Column>
    </DataTable>

    <Dialog v-model:visible="dialogVisible" modal :header="isNew ? 'Новое сообщение' : 'Редактирование сообщения'" style="width: 40rem">
        <div class="flex flex-column gap-5 pt-3">
            <FloatLabel>
                <InputText v-model="form.address" id="queueAddress" class="w-full"/>
                <label for="queueAddress">{{ type === 'email' ? 'Email' : 'Телефон' }}</label>
            </FloatLabel>

            <FloatLabel>
                <Textarea v-model="form.body" id="queueBody" rows="6" autoResize class="w-full"/>
                <label for="queueBody">Текст</label>
            </FloatLabel>
        </div>

        <template #footer>
            <Button label="Отмена" severity="secondary" outlined @click="dialogVisible = false"/>
            <Button label="Сохранить" :loading="saving" @click="save"/>
        </template>
    </Dialog>
</template>

<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import Toolbar from 'primevue/toolbar'
import Button from 'primevue/button'
import SelectButton from 'primevue/selectbutton'
import Calendar from 'primevue/calendar'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import FloatLabel from 'primevue/floatlabel'
import {showError, showSuccess} from 'app/toast'
import {messagingAPI} from 'cmp/messages'

const typeOptions = [
    {value: 'sms', label: 'SMS'},
    {value: 'email', label: 'Email'},
];

const type = ref('sms');

const presetOptions = [
    {value: 'today', label: 'Сегодня'},
    {value: 'week', label: 'Неделя'},
    {value: 'range', label: 'Период'},
];

const preset = ref('today');
const customRange = ref(null);

const formatDate = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');

    return `${y}-${m}-${d}`;
};

// "Все неотправленные" — 4-е состояние фильтра, но не входит в presetOptions: это отдельная
// кнопка, а не переключатель наравне с "Сегодня"/"Неделя"/"Период" (так попросили — визуально
// это действие, а не ещё один режим в той же группе).
const range = computed(() => {
    if (preset.value === 'today') {
        const today = formatDate(new Date());
        return {from: today, to: today};
    }

    if (preset.value === 'week') {
        const today = new Date();
        const weekAgo = new Date(today);
        weekAgo.setDate(weekAgo.getDate() - 6);
        return {from: formatDate(weekAgo), to: formatDate(today)};
    }

    if (preset.value === 'range' && customRange.value?.[0] && customRange.value?.[1]) {
        return {from: formatDate(customRange.value[0]), to: formatDate(customRange.value[1])};
    }

    // 'range' без выбранных обеих дат либо 'all' — без ограничения по дате.
    return {from: null, to: null};
});

const messages = ref([]);
const loading = ref(false);
const sending = ref(false);
const txtUrl = computed(() => messagingAPI.dailyTxtUrl(type.value, range.value));

const load = () => {
    loading.value = true;

    messagingAPI.daily(type.value, range.value)
        .then((response) => {
            messages.value = response.data.messages;
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось загрузить список.');
        })
        .finally(() => {
            loading.value = false;
        });
};

onMounted(load);
watch([type, range], load);

const send = () => {
    sending.value = true;

    messagingAPI.dailySend(type.value)
        .then((response) => {
            const status = response.data.response?.status;
            showSuccess(`Отправлено. Статус ответа шлюза: ${status}.`);
            load();
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось отправить рассылку.');
        })
        .finally(() => {
            sending.value = false;
        });
};

// --- Диалог создания/редактирования одной строки очереди ---

const dialogVisible = ref(false);
const isNew = ref(true);
const saving = ref(false);
const editingId = ref(null);

const form = reactive({
    address: '',
    body: '',
});

const openCreate = () => {
    isNew.value = true;
    editingId.value = null;
    Object.assign(form, {address: '', body: ''});
    dialogVisible.value = true;
};

const openEdit = (row) => {
    isNew.value = false;
    editingId.value = row.id;
    Object.assign(form, {address: row.address, body: row.body});
    dialogVisible.value = true;
};

const save = () => {
    saving.value = true;

    const request = isNew.value
        ? messagingAPI.createDaily({type: type.value, address: form.address, body: form.body})
        : messagingAPI.updateDaily(editingId.value, {address: form.address, body: form.body});

    request
        .then(() => {
            showSuccess('Сообщение сохранено.');
            dialogVisible.value = false;
            load();
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось сохранить сообщение.'))
        .finally(() => saving.value = false);
};

const remove = (row) => {
    if (!window.confirm(`Удалить сообщение для "${row.address}"?`)) return;

    messagingAPI.deleteDaily(row.id)
        .then(() => {
            showSuccess('Сообщение удалено.');
            load();
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось удалить сообщение.'));
};
</script>
