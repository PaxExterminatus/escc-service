<template>
    <LazyPanel
        title="Доступные теги"
        icon="pi pi-tags"
        heading-tag="h2"
        v-model:collapsed="collapsed"
        :loading="loading"
        @reload="loadTags"
        @expand="loadTags"
    >
        <div class="flex flex-column gap-2 min-w-0">
            <!-- Место для спецтегов конкретного редактора (например {BODY} у обёрток письма) -->
            <slot name="before"/>

            <div v-if="resolvedContainer" class="text-xs text-color-secondary">
                Посылка {{ resolvedContainer.container_code }}
                <a href="#" @click.prevent="openPicker(null)">(сменить)</a>
            </div>
            <TagTree :tags="tags" @select="onSelectTag"/>
        </div>
    </LazyPanel>

    <Dialog v-model:visible="pickerVisible" modal header="Выберите посылку" style="width: 45rem">
        <ClientCoursesPanel :client-id="clientId" selectable @select-container="onContainerPicked"/>
    </Dialog>
</template>

<script setup>
import {defineEmits, defineProps, ref} from 'vue'
import Dialog from 'primevue/dialog'
import {LazyPanel} from 'cmp/element'
import {tagAPI, TagTree} from 'cmp/tags'
import {ClientCoursesPanel} from 'cmp/course'
import {TagScope} from 'app/enums'
import {tagPlaceholder} from 'utils/tag'
import {showError} from 'app/toast'

const props = defineProps({
    // Если задан — известен клиент (панель отправки сообщения), доступны и теги уровня
    // "Счёт" (через пикер курс→посылка). Без clientId (абстрактный редактор шаблонов) —
    // только client-теги.
    clientId: {type: [String, Number], default: null},
});

const emit = defineEmits({
    insert: null,
});

// Свёрнуто по умолчанию — список тегов нужен не постоянно, а место при наборе текста важнее.
const collapsed = ref(true);
const loading = ref(false);
const tags = ref([]);

const loadTags = () => {
    loading.value = true;

    tagAPI.index()
        .then((response) => {
            tags.value = props.clientId
                ? response.data.tags
                : response.data.tags.filter((tag) => tag.scope === TagScope.client);
        })
        .catch(() => showError('Не удалось загрузить список тегов.'))
        .finally(() => {
            loading.value = false;
        });
};

// Тегу уровня "Счёт" нужен конкретный контейнер — выбирается один раз через пикер
// (курс → посылка), дальше подставляется уже готовое значение, без диалога повторно.
const pickerVisible = ref(false);
const pendingTag = ref(null);
const resolvedContainer = ref(null);
const resolvedTags = ref(null);

const openPicker = (tag) => {
    pendingTag.value = tag;
    resolvedContainer.value = null;
    resolvedTags.value = null;
    pickerVisible.value = true;
};

/** Значение тега для вставки: разрешённое по выбранной посылке либо сам плейсхолдер */
const valueFor = (tag) => resolvedTags.value?.[tag.code] ?? tagPlaceholder(tag.code);

const onSelectTag = (tag) => {
    if (tag.scope !== TagScope.invoice || !props.clientId) {
        emit('insert', tagPlaceholder(tag.code));
        return;
    }

    resolvedTags.value ? emit('insert', valueFor(tag)) : openPicker(tag);
};

const onContainerPicked = (container) => {
    pickerVisible.value = false;
    resolvedContainer.value = container;

    tagAPI.forContainer(container.container_id)
        .then((response) => {
            resolvedTags.value = response.data.tags;

            if (pendingTag.value) {
                emit('insert', valueFor(pendingTag.value));
                pendingTag.value = null;
            }
        })
        .catch(() => showError('Не удалось получить значения тегов по посылке.'));
};
</script>
