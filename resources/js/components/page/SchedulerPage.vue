<template>
    <Toolbar>
        <template #start>
            <h1>Планировщик задач</h1>
        </template>
    </Toolbar>

    <p class="text-color-secondary text-sm mt-0">
        Список задач фиксирован кодом, здесь настраивается только время запуска и признак
        включённости. Чтобы расписание реально срабатывало без участия оператора, на сервере
        должен быть настроен системный вызов <code>php artisan schedule:run</code> раз в минуту.
    </p>

    <DataTable :value="tasks" :loading="loading" dataKey="task_id">
        <Column field="label" header="Задача"/>
        <Column field="command" header="Команда"/>
        <Column field="run_time" header="Время запуска"/>
        <Column header="Включена">
            <template #body="{data}">
                <span :class="data.is_enabled ? 'pi pi-check text-green-400' : 'pi pi-times text-red-400'"></span>
            </template>
        </Column>
        <Column header="">
            <template #body="{data}">
                <Button icon="pi pi-pencil" text v-tooltip.left="'Редактировать'" @click="openEdit(data)"/>
            </template>
        </Column>
    </DataTable>

    <Dialog v-model:visible="dialogVisible" modal header="Расписание задачи" style="width: 30rem">
        <div class="flex flex-column gap-5 pt-3">
            <div class="text-color-secondary">{{ form.label }}</div>

            <FloatLabel>
                <InputText v-model="form.run_time" id="taskRunTime" class="w-full" placeholder="ЧЧ:ММ"/>
                <label for="taskRunTime">Время запуска (ЧЧ:ММ)</label>
            </FloatLabel>

            <div class="flex align-items-center gap-2">
                <Checkbox v-model="form.is_enabled" :binary="true" inputId="taskIsEnabled"/>
                <label for="taskIsEnabled">Включена</label>
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
import Checkbox from 'primevue/checkbox'
import FloatLabel from 'primevue/floatlabel'
import {showError, showSuccess} from 'app/toast'
import {schedulerAPI} from 'cmp/scheduler'

const tasks = ref([]);
const loading = ref(false);

const load = () => {
    loading.value = true;

    schedulerAPI.index()
        .then((response) => {
            tasks.value = response.data.tasks;
        })
        .catch(() => showError('Не удалось загрузить список задач.'))
        .finally(() => {
            loading.value = false;
        });
};

onMounted(load);

const dialogVisible = ref(false);
const saving = ref(false);
const editingId = ref(null);

const form = reactive({
    label: '',
    run_time: '',
    is_enabled: true,
});

const openEdit = (row) => {
    editingId.value = row.task_id;
    Object.assign(form, {label: row.label, run_time: row.run_time, is_enabled: row.is_enabled});
    dialogVisible.value = true;
};

const save = () => {
    saving.value = true;

    schedulerAPI.update(editingId.value, {run_time: form.run_time, is_enabled: form.is_enabled})
        .then(() => {
            showSuccess('Расписание сохранено.');
            dialogVisible.value = false;
            load();
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось сохранить расписание.'))
        .finally(() => saving.value = false);
};
</script>
