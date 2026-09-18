<template>
    <Toolbar>
        <template #start>
            <h1>Теги Данных</h1>
        </template>
    </Toolbar>

    <p class="text-color-secondary mt-3 mb-4">
        Полный список тегов-плейсхолдеров для шаблонов сообщений и документов. Тот же список отдаёт
        <code>GET /api/tags</code> — этим API пользуется в т.ч. внешнее Word-расширение. Клик по тегу
        копирует его в буфер обмена.
    </p>

    <TagTree :tags="tags" @select="copyTag"/>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import Toolbar from 'primevue/toolbar'
import {tagAPI, TagTree} from 'cmp/tags'
import {showError, showSuccess} from 'app/toast'

const tags = ref([]);

onMounted(() => {
    tagAPI.index()
        .then((response) => {
            tags.value = response.data.tags;
        });
});

const copyTag = (tag) => {
    const placeholder = `{${tag.code}}`;

    navigator.clipboard.writeText(placeholder)
        .then(() => {
            showSuccess(`Скопировано: ${placeholder}`);
        })
        .catch(() => {
            showError('Не удалось скопировать в буфер обмена.');
        });
};
</script>
