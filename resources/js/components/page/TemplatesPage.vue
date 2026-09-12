<template>
    <Toolbar>
        <template #start>
            <h1>Шаблоны сообщений</h1>
        </template>
        <template #end>
            <Button label="Добавить" icon="pi pi-plus" @click="openCreate"/>
        </template>
    </Toolbar>

    <DataTable :value="templates" :loading="loading" dataKey="id">
        <Column field="code" header="Код"/>
        <Column field="name" header="Название"/>
        <Column field="body" header="Текст"/>
        <Column field="is_active" header="Активен">
            <template #body="{data}">
                <span :class="data.is_active ? 'pi pi-check text-green-400' : 'pi pi-times text-red-400'"></span>
            </template>
        </Column>
        <Column header="">
            <template #body="{data}">
                <Button icon="pi pi-pencil" text v-tooltip.top="'Редактировать'" @click="openEdit(data)"/>
                <Button icon="pi pi-trash" text severity="danger" v-tooltip.top="'Удалить'" @click="remove(data)"/>
            </template>
        </Column>
    </DataTable>

    <Dialog v-model:visible="dialogVisible" modal :header="isNew ? 'Новый шаблон' : 'Редактирование шаблона'" style="width: 40rem">
        <div class="flex flex-column gap-3">
            <FloatLabel>
                <InputText v-model="form.code" id="templateCode" class="w-full"/>
                <label for="templateCode">Код</label>
            </FloatLabel>

            <FloatLabel>
                <InputText v-model="form.name" id="templateName" class="w-full"/>
                <label for="templateName">Название</label>
            </FloatLabel>

            <FloatLabel>
                <Textarea v-model="form.body" id="templateBody" rows="4" autoResize class="w-full"/>
                <label for="templateBody">Текст (плейсхолдеры вида {amount})</label>
            </FloatLabel>

            <div class="flex align-items-center gap-2">
                <Checkbox v-model="form.is_active" :binary="true" inputId="templateIsActive"/>
                <label for="templateIsActive">Активен</label>
            </div>
        </div>

        <template #footer>
            <Button label="Отмена" severity="secondary" outlined @click="dialogVisible = false"/>
            <Button label="Сохранить" :loading="saving" @click="save"/>
        </template>
    </Dialog>
</template>

<script setup>
import {onMounted, reactive, ref} from 'vue'
import Toolbar from 'primevue/toolbar'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'
import FloatLabel from 'primevue/floatlabel'
import {showError, showSuccess} from 'app/toast'
import {messagingAPI} from 'cmp/messages'

const templates = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const saving = ref(false);
const isNew = ref(true);
const form = reactive({id: null, code: '', name: '', body: '', is_active: true});

const load = () => {
    loading.value = true;

    messagingAPI.templates(false)
        .then((response) => {
            templates.value = response.data.data;
        })
        .finally(() => {
            loading.value = false;
        });
};

onMounted(load);

const openCreate = () => {
    isNew.value = true;
    Object.assign(form, {id: null, code: '', name: '', body: '', is_active: true});
    dialogVisible.value = true;
};

const openEdit = (template) => {
    isNew.value = false;
    Object.assign(form, template);
    dialogVisible.value = true;
};

const save = () => {
    saving.value = true;

    const payload = {code: form.code, name: form.name, body: form.body, is_active: form.is_active};
    const request = isNew.value ? messagingAPI.createTemplate(payload) : messagingAPI.updateTemplate(form.id, payload);

    request
        .then(() => {
            showSuccess('Шаблон сохранён.');
            dialogVisible.value = false;
            load();
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось сохранить шаблон.');
        })
        .finally(() => {
            saving.value = false;
        });
};

const remove = (template) => {
    if (!window.confirm(`Удалить шаблон "${template.name}"?`)) return;

    messagingAPI.deleteTemplate(template.id)
        .then(() => {
            showSuccess('Шаблон удалён.');
            load();
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось удалить шаблон.');
        });
};
</script>
