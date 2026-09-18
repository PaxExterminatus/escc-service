<template>
    <Toolbar>
        <template #start>
            <h1>Шаблоны</h1>
        </template>
    </Toolbar>

    <p class="text-color-secondary text-sm mt-0">
        Один список для всех шаблонов — свободных (оператор выбирает вручную при отправке),
        привязанных к операции системы (печать/письмо по счёту) и обёрток письма (шапка+футер).
        Назначение указывается при создании и определяет, для чего доступен шаблон. У шаблонов
        с содержимым можно выбрать конкретную обёртку, оставить «по умолчанию» либо выбрать
        «Автоматически» — тогда цвет письма подбирается по факту задолженности клиента (обёртки
        с ролью «Задолженность»/«Без задолженности»). В самой обёртке используйте спецтег
        <code>{{ BODY_TAG }}</code> — на его место подставится тело письма.
    </p>

    <div class="flex align-items-center justify-content-between mb-3">
        <SelectButton v-model="mediaFilter" :options="mediaFilterOptions" optionLabel="label" optionValue="value"/>
        <Button label="Добавить" icon="pi pi-plus" @click="openCreate"/>
    </div>

    <DataTable :value="filteredRows" dataKey="key" :loading="loading">
        <Column field="purpose_label" header="Назначение"/>

        <Column header="Носитель">
            <template #body="{data}">
                <div v-if="data.purpose === 'operation'" class="flex flex-column gap-2">
                    <span v-for="slot in data.slots" :key="slot.type_id">{{ slot.type_label }}</span>
                </div>
                <span v-else>{{ data.type_label }}</span>
            </template>
        </Column>

        <Column header="Шаблон">
            <template #body="{data}">
                <div v-if="data.purpose === 'operation'" class="flex flex-column gap-2">
                    <span v-for="slot in data.slots" :key="slot.type_id">
                        <span v-if="slot.name">{{ slot.name }}</span>
                        <span v-else class="text-color-secondary">{{ slot.has_default ? 'Стандартный бланк' : 'Не назначен' }}</span>
                    </span>
                </div>
                <template v-else>
                    <span v-if="data.name">{{ data.name }}</span>
                    <span v-else class="text-color-secondary">
                        {{ data.has_default ? 'Стандартный бланк' : 'Не назначен' }}
                    </span>
                </template>
            </template>
        </Column>

        <Column header="Обёртка письма">
            <template #body="{data}">
                <span v-if="data.purpose === 'wrapper'" :class="data.is_default || data.wrapper_role ? 'text-primary' : 'text-color-secondary'">
                    {{ wrapperRowLabel(data) }}
                </span>
                <div v-else-if="data.purpose === 'operation'" class="flex flex-column gap-2">
                    <div v-for="slot in data.slots" :key="slot.type_id" class="flex flex-column">
                        <span v-for="(line, i) in wrapperCellLines(slot)" :key="i" :class="line.link ? 'text-primary' : 'text-color-secondary'">
                            <span v-if="line.link" class="pi pi-link mr-1" v-tooltip.top="'Применяется в зависимости от баланса клиента'"></span>{{ line.text }}
                        </span>
                    </div>
                </div>
                <div v-else class="flex flex-column">
                    <span v-for="(line, i) in wrapperCellLines(data)" :key="i" :class="line.link ? 'text-primary' : 'text-color-secondary'">
                        <span v-if="line.link" class="pi pi-link mr-1" v-tooltip.top="'Применяется в зависимости от баланса клиента'"></span>{{ line.text }}
                    </span>
                </div>
            </template>
        </Column>

        <Column header="Активен">
            <template #body="{data}">
                <span v-if="data.purpose !== 'operation'" :class="data.is_active ? 'pi pi-check text-green-400' : 'pi pi-times text-red-400'"></span>
                <span v-else class="text-color-secondary">—</span>
            </template>
        </Column>

        <Column header="">
            <template #body="{data}">
                <div v-if="data.purpose === 'operation'" class="flex flex-column gap-2">
                    <div v-for="slot in data.slots" :key="slot.type_id">
                        <Button icon="pi pi-eye" text v-tooltip.left="'Просмотр'" @click="openPreview(operationSlotRow(data, slot))"/>
                        <Button icon="pi pi-pencil" text v-tooltip.left="'Редактировать'" @click="openRowEdit(operationSlotRow(data, slot))"/>
                    </div>
                </div>
                <template v-else>
                    <Button icon="pi pi-eye" text v-tooltip.left="'Просмотр'" @click="openPreview(data)"/>
                    <Button icon="pi pi-pencil" text v-tooltip.left="'Редактировать'" @click="openRowEdit(data)"/>
                    <Button v-if="data.id" icon="pi pi-trash" text severity="danger" v-tooltip.left="'Удалить'" @click="remove(data)"/>
                </template>
            </template>
        </Column>
    </DataTable>

    <Dialog v-model:visible="dialogVisible" modal :header="isNew ? 'Новый шаблон' : 'Редактирование шаблона'" style="width: 55rem">
        <div class="flex flex-column gap-5 pt-3">
            <FloatLabel>
                <Dropdown
                    v-model="form.purpose"
                    :options="purposeOptions"
                    optionLabel="label"
                    optionValue="value"
                    inputId="templatePurpose"
                    class="w-full"
                    :disabled="!isNew"
                />
                <label for="templatePurpose">Назначение</label>
            </FloatLabel>

            <template v-if="kind !== 'operation'">
                <FloatLabel>
                    <InputText v-model="form.code" id="templateCode" class="w-full"/>
                    <label for="templateCode">Код</label>
                </FloatLabel>
            </template>

            <FloatLabel>
                <InputText v-model="form.name" id="templateName" class="w-full"/>
                <label for="templateName">Название</label>
            </FloatLabel>

            <!-- docx-шаблон операции задаётся файлом, всё остальное — текстом/разметкой -->
            <div v-if="isDocx" class="flex align-items-center gap-3">
                <input type="file" accept=".docx" @change="onFileChange"/>
                <!-- Настоящая ссылка, не Button: скачивание — это навигация браузера на файл
                     с Content-Disposition: attachment (см. TemplateController::operationFile), а
                     не JS-обработчик, как у Скачать/Загрузить для HTML ниже. -->
                <a :href="templateAPI.operationFileUrl(operationSlot[0])" class="flex align-items-center gap-1 text-sm">
                    <span class="pi pi-download"></span>Скачать текущий файл
                </a>
            </div>

            <SmsBodyEditor
                v-else-if="kind === 'free'"
                v-model="form.body"
                id="templateBody"
                label="Текст (плейсхолдеры вида {amount})"
            />

            <div v-else class="flex gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex justify-content-end gap-2 mb-2">
                        <Button label="Скачать" icon="pi pi-download" text size="small" @click="downloadBody"/>
                        <Button label="Загрузить" icon="pi pi-upload" text size="small" @click="bodyFileInput.click()"/>
                        <input ref="bodyFileInput" type="file" accept=".html,.htm,.txt" class="hidden" @change="onBodyFileChosen"/>
                    </div>
                    <FloatLabel>
                        <Textarea v-model="form.body" id="templateHtmlBody" rows="8" autoResize class="w-full"/>
                        <label for="templateHtmlBody">HTML</label>
                    </FloatLabel>
                </div>
                <div style="width: 16rem; flex-shrink: 0;">
                    <TagInsertPanel @insert="insertValue">
                        <template v-if="kind === 'wrapper'" #before>
                            <a href="#" class="tag-chip" @click.prevent="insertValue(BODY_TAG)">{{ BODY_TAG }}</a>
                        </template>
                    </TagInsertPanel>
                </div>
            </div>

            <FloatLabel v-if="supportsWrapper">
                <Dropdown
                    v-model="wrapperSelection"
                    :options="wrapperOptions"
                    optionLabel="label"
                    optionValue="value"
                    inputId="templateWrapper"
                    class="w-full"
                />
                <label for="templateWrapper">Обёртка письма</label>
            </FloatLabel>

            <template v-if="kind === 'wrapper'">
                <div class="flex align-items-center gap-2">
                    <Checkbox v-model="form.is_default" :binary="true" inputId="wrapperIsDefault"/>
                    <label for="wrapperIsDefault">Обёртка по умолчанию (одна на систему)</label>
                </div>

                <FloatLabel>
                    <Dropdown
                        v-model="form.wrapper_role"
                        :options="wrapperRoleOptions"
                        optionLabel="label"
                        optionValue="value"
                        inputId="wrapperRole"
                        class="w-full"
                    />
                    <label for="wrapperRole">Роль в авто-выборе по балансу</label>
                </FloatLabel>
            </template>

            <div v-if="kind !== 'operation'" class="flex align-items-center gap-2">
                <Checkbox v-model="form.is_active" :binary="true" inputId="templateIsActive"/>
                <label for="templateIsActive">Активен</label>
            </div>
        </div>

        <template #footer>
            <Button label="Отмена" severity="secondary" outlined @click="dialogVisible = false"/>
            <Button label="Сохранить" :loading="saving" @click="save"/>
        </template>
    </Dialog>

    <DocxPreviewDialog
        v-model:visible="docxPreviewVisible"
        header="Предпросмотр шаблона"
        :url="docxPreviewUrl"
    />

    <HtmlPreviewDialog
        v-model:visible="htmlPreviewVisible"
        :header="htmlPreviewTitle"
        :html="htmlPreviewContent"
        :loading="htmlPreviewLoading"
    />
</template>

<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import Toolbar from 'primevue/toolbar'
import Button from 'primevue/button'
import SelectButton from 'primevue/selectbutton'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'
import FloatLabel from 'primevue/floatlabel'
import {showError, showSuccess} from 'app/toast'
import {BODY_TAG, TemplateType, TemplateWrapperRole} from 'app/enums'
import {appendTag} from 'utils/tag'
import {downloadText, readTextFile} from 'utils/file'
import {SmsBodyEditor} from 'cmp/messages'
import {templateAPI} from 'cmp/templates'
import {TagInsertPanel} from 'cmp/tags'
import {DocxPreviewDialog, HtmlPreviewDialog} from 'element'

const route = useRoute();
const router = useRouter();

// --- Источники данных ---

const templates = ref([]);
const wrappers = ref([]);
const operations = ref([]);
const loading = ref(false);

const load = () => {
    loading.value = true;

    Promise.all([
        templateAPI.index(false).then((r) => templates.value = r.data.data),
        templateAPI.wrappers().then((r) => wrappers.value = r.data.data),
        templateAPI.operations().then((r) => operations.value = r.data.operations),
    ])
        .then(openFromRoute)
        .catch(() => showError('Не удалось загрузить шаблоны.'))
        .finally(() => loading.value = false);
};

onMounted(load);

// --- Единый список строк таблицы: операции (одна строка = одна операция, носители внутри
// неё) + свободные шаблоны + обёртки ---

// Раньше на каждый носитель операции была своя строка таблицы ("Печать счёта" и "Счёт по
// email" — два ничем визуально не связанных ряда), и значок-цепь у обеих подсказывал, что это
// один документ. Проблема: при появлении второй такой операции все её строки получили бы тот
// же значок — видно, что "что-то с чем-то связано", но не видно, с чем конкретно. Настоящая
// группировка (одна строка на операцию, носители — подстроки внутри) масштабируется без этой
// проблемы: связь видна из структуры, а не из повторяющейся иконки.
const operationRows = computed(() => operations.value.map((operation) => ({
    key: `op_${operation.operation_id}`,
    purpose: 'operation',
    purpose_label: operation.group_label,
    operation_id: operation.operation_id,
    slots: operation.slots.map((slot) => ({
        type_id: slot.type_id,
        type_label: slot.type_label,
        operation_label: slot.operation_label,
        has_default: slot.has_default,
        ...templateFields(slot.template),
    })),
})));

/** Данные слота операции в форме, ожидаемой openPreview/openRowEdit (как обычная строка) */
const operationSlotRow = (operationRow, slot) => ({
    ...slot,
    purpose: 'operation',
    purpose_label: slot.operation_label,
    operation_id: operationRow.operation_id,
});

const freeRows = computed(() => templates.value.map((t) => ({
    key: `free_${t.id}`,
    purpose: 'free',
    purpose_label: 'Свободный (ручная отправка)',
    type_label: 'Текст (SMS/Email)',
    ...templateFields(t),
})));

const wrapperRows = computed(() => wrappers.value.map((w) => ({
    key: `wrapper_${w.id}`,
    purpose: 'wrapper',
    purpose_label: 'Обёртка письма',
    type_label: 'Обёртка (HTML)',
    ...templateFields(w),
})));

const allRows = computed(() => [...operationRows.value, ...freeRows.value, ...wrapperRows.value]);

// --- Фильтр по носителю: "Обёртка" (шапка+футер) vs "Тело" (всё, что в неё вставляется) ---

const mediaFilterOptions = [
    {value: 'all', label: 'Все'},
    {value: 'body', label: 'Тело'},
    {value: 'wrapper', label: 'Обёртка'},
];

const mediaFilter = ref('all');

const filteredRows = computed(() => {
    if (mediaFilter.value === 'all') return allRows.value;

    return allRows.value.filter((row) => (row.purpose === 'wrapper') === (mediaFilter.value === 'wrapper'));
});

// Обёртки с ролью — те, что реально могут быть выбраны авто-подбором по балансу (см.
// EmailComposer::wrapperForBalance); показываем их поимённо у шаблонов с WRAPPER_AUTO — каждую
// на своей строке, чтобы "Автоматически" не выглядело чёрным ящиком.
const autoWrapperCandidates = computed(() => wrappers.value.filter((w) => w.wrapper_role));

/**
 * Строки колонки "Обёртка письма" для одного content-шаблона: обычно одна строка, но у
 * WRAPPER_AUTO — по одной на каждую обёртку-кандидата (см. autoWrapperCandidates).
 *
 * @return {Array<{text: string, link: boolean}>}
 */
const wrapperCellLines = (row) => {
    if (row.wrapper_auto) {
        return autoWrapperCandidates.value.length
            ? autoWrapperCandidates.value.map((w) => ({text: w.name, link: true}))
            : [{text: 'Автоматически (по балансу)', link: true}];
    }

    return [{text: row.effective_wrapper_name ?? '—', link: false}];
};

/** Поля из TemplateResource — одинаковы для всех трёх источников, шаблон может отсутствовать */
const templateFields = (template) => ({
    id: template?.id ?? null,
    code: template?.code ?? '',
    name: template?.name ?? null,
    body: template?.body ?? '',
    wrapper_id: template?.wrapper_id ?? null,
    wrapper_auto: template?.wrapper_auto ?? false,
    wrapper_role: template?.wrapper_role ?? null,
    effective_wrapper_name: template?.effective_wrapper_name ?? null,
    is_default: template?.is_default ?? false,
    is_active: template?.is_active ?? true,
});

/** Подпись роли для строки-обёртки в таблице */
const wrapperRowLabel = (row) => {
    if (row.wrapper_role === TemplateWrapperRole.debt) return 'Роль: задолженность';
    if (row.wrapper_role === TemplateWrapperRole.positive) return 'Роль: без задолженности';
    return row.is_default ? 'По умолчанию' : '—';
};

const purposeOptions = computed(() => [
    {value: 'free', label: 'Свободный (ручная отправка)'},
    ...operations.value.flatMap((operation) => operation.slots.map((slot) => ({
        value: `${operation.operation_id}:${slot.type_id}`,
        label: slot.operation_label,
    }))),
    {value: 'wrapper', label: 'Обёртка письма (новый шаблон)'},
]);

const wrapperOptions = computed(() => [
    {value: null, label: 'По умолчанию'},
    {value: 'auto', label: 'Автоматически (по балансу счёта)'},
    ...wrappers.value.map((w) => ({value: w.id, label: w.name + (w.is_default ? ' (по умолчанию)' : '')})),
]);

const wrapperRoleOptions = [
    {value: null, label: 'Без роли'},
    {value: TemplateWrapperRole.debt, label: 'Задолженность (для авто-выбора)'},
    {value: TemplateWrapperRole.positive, label: 'Без задолженности (для авто-выбора)'},
];

// --- Диалог создания/редактирования ---

const dialogVisible = ref(false);
const isNew = ref(true);
const saving = ref(false);
const editingId = ref(null);

const form = reactive({
    purpose: 'free',
    code: '',
    name: '',
    body: '',
    is_active: true,
    is_default: false,
    wrapper_id: null,
    wrapper_auto: false,
    wrapper_role: null,
    file: null,
});

// Один и тот же дропдаун "Обёртка письма" управляет двумя полями формы сразу: явным
// wrapper_id и флагом wrapper_auto ("Автоматически") — они взаимоисключающие.
const wrapperSelection = computed({
    get: () => form.wrapper_auto ? 'auto' : form.wrapper_id,
    set: (value) => {
        form.wrapper_auto = value === 'auto';
        form.wrapper_id = value === 'auto' ? null : value;
    },
});

// purpose кодирует одно из трёх назначений: свободный шаблон, обёртка, либо конкретный
// слот операции в виде "operationId:typeId"
const kind = computed(() => ['free', 'wrapper'].includes(form.purpose) ? form.purpose : 'operation');
const operationSlot = computed(() => kind.value === 'operation' ? form.purpose.split(':').map(Number) : [null, null]);
const isDocx = computed(() => operationSlot.value[1] === TemplateType.docx);
// Обёртку можно выбрать только тому, что само становится телом письма
const supportsWrapper = computed(() => kind.value === 'free' || (kind.value === 'operation' && !isDocx.value));

const blankForm = {purpose: 'free', code: '', name: '', body: '', is_active: true, is_default: false, wrapper_id: null, wrapper_auto: false, wrapper_role: null, file: null};

const openCreate = () => {
    isNew.value = true;
    editingId.value = null;
    Object.assign(form, blankForm);
    dialogVisible.value = true;
};

const openRowEdit = (row, updateUrl = true) => {
    isNew.value = false;
    editingId.value = row.purpose === 'operation' ? null : row.id;

    Object.assign(form, {
        ...blankForm,
        purpose: row.purpose === 'operation' ? `${row.operation_id}:${row.type_id}` : row.purpose,
        code: row.code,
        name: row.name ?? row.purpose_label,
        body: row.body,
        is_active: row.is_active,
        is_default: row.is_default,
        wrapper_id: row.wrapper_id,
        wrapper_auto: row.wrapper_auto,
        wrapper_role: row.wrapper_role,
    });

    dialogVisible.value = true;

    if (updateUrl && row.purpose === 'free') {
        router.push({name: 'messagesTemplates', params: {code: row.code}});
    }
};

// Шаблон можно открыть прямой ссылкой .../templates/{code}; при закрытии код уходит из URL
const openFromRoute = () => {
    const code = route.params.code;

    if (!code) return;

    const row = freeRows.value.find((r) => r.code === code);

    row ? openRowEdit(row, false) : showError(`Шаблон с кодом "${code}" не найден.`);
};

watch(dialogVisible, (visible) => {
    if (!visible && route.params.code) {
        router.push({name: 'messagesTemplates'});
    }
});

const onFileChange = (event) => {
    form.file = event.target.files[0] ?? null;
};

const insertValue = (value) => {
    form.body = appendTag(form.body, value);
};

// Скачать/загрузить HTML-тело как файл — чтобы можно было править во внешнем редакторе, не
// теряя при этом обычное редактирование прямо в textarea (см. downloadText/readTextFile).
const bodyFileInput = ref(null);

const downloadBody = () => {
    downloadText(`${form.code || form.name || 'template'}.html`, form.body ?? '', 'text/html');
};

const onBodyFileChosen = (event) => {
    readTextFile(event)
        .then((text) => {
            if (text !== null) form.body = text;
        })
        .finally(() => {
            event.target.value = '';
        });
};

const save = () => {
    saving.value = true;

    const [operationId, typeId] = operationSlot.value;

    let request;

    if (kind.value === 'operation') {
        const content = isDocx.value ? {file: form.file} : {body: form.body};
        request = templateAPI.assignOperation(operationId, typeId, form.name, content, form.wrapper_id, form.wrapper_auto);
    } else {
        const payload = {
            code: form.code,
            name: form.name,
            body: form.body,
            is_active: form.is_active,
            ...(kind.value === 'wrapper'
                ? {is_default: form.is_default, wrapper_role: form.wrapper_role, type_id: TemplateType.wrapper}
                : {wrapper_id: form.wrapper_id, wrapper_auto: form.wrapper_auto}),
        };

        request = editingId.value ? templateAPI.update(editingId.value, payload) : templateAPI.create(payload);
    }

    request
        .then(() => {
            showSuccess('Шаблон сохранён.');
            dialogVisible.value = false;
            load();
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось сохранить шаблон.'))
        .finally(() => saving.value = false);
};

const remove = (row) => {
    if (!window.confirm(`Удалить шаблон "${row.name}"?`)) return;

    templateAPI.delete(row.id)
        .then(() => {
            showSuccess('Шаблон удалён.');
            load();
        })
        .catch((error) => showError(error.response?.data?.message ?? 'Не удалось удалить шаблон.'));
};

// --- Предпросмотр: docx отдаётся файлом, всё остальное собирает сервер ---

const docxPreviewVisible = ref(false);
const docxPreviewUrl = ref(null);

const htmlPreviewVisible = ref(false);
const htmlPreviewLoading = ref(false);
const htmlPreviewTitle = ref('Предпросмотр');
const htmlPreviewContent = ref('');

const openPreview = (row) => {
    if (row.type_id === TemplateType.docx) {
        docxPreviewUrl.value = templateAPI.operationFileUrl(row.operation_id);
        docxPreviewVisible.value = true;
        return;
    }

    if (!row.id) {
        showError('Шаблон ещё не назначен — предпросматривать нечего.');
        return;
    }

    htmlPreviewTitle.value = row.purpose === 'wrapper' ? 'Предпросмотр обёртки письма' : 'Предпросмотр письма';
    htmlPreviewContent.value = '';
    htmlPreviewLoading.value = true;
    htmlPreviewVisible.value = true;

    templateAPI.preview(row.id)
        .then((response) => htmlPreviewContent.value = response.data.html)
        .catch(() => {
            htmlPreviewVisible.value = false;
            showError('Не удалось загрузить предпросмотр.');
        })
        .finally(() => htmlPreviewLoading.value = false);
};
</script>

<style scoped>
.tag-chip {
    display: inline-block;
    font-family: ui-monospace, Consolas, monospace;
    font-size: 0.8rem;
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    background: var(--surface-100, rgba(255, 255, 255, 0.06));
    color: var(--text-color);
    text-decoration: none;
}

.tag-chip:hover {
    background: var(--primary-color);
    color: var(--primary-color-text);
}
</style>
