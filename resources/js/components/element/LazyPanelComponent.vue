<template>
    <Panel toggleable :collapsed="collapsed" @update:collapsed="setCollapsed">
        <template #header>
            <component :is="headingTag" class="cursor-pointer flex align-items-center gap-2 lazy-panel-heading" @click="setCollapsed(!collapsed)">
                <span v-if="icon" :class="icon"></span>
                {{ title }}
            </component>
        </template>
        <template v-if="reloadable" #icons>
            <button
                type="button"
                class="p-link p-panel-header-icon p-panel-toggler"
                :disabled="loading"
                v-tooltip.left="'Обновить принудительно'"
                @click="$emit('reload')"
            >
                <span :class="loading ? 'pi pi-spinner pi-spin' : 'pi pi-refresh'"></span>
            </button>
        </template>

        <slot v-if="opened"/>
    </Panel>
</template>

<script setup>
/**
 * Общий вид вкладки профиля: заголовок (+ иконка) со сворачиванием, кнопка принудительного
 * обновления и ленивая подгрузка содержимого — раньше эта разметка (Panel + #icons + v-if по
 * "opened") была скопирована в каждую вкладку по отдельности. Теперь один компонент — и для
 * страничных секций (Курсы/Сообщения/Финансы, h1), и для вложенных (Доступные теги, h2).
 *
 * Заголовок здесь именно кликабельный <h1>/<h2>, а не декоративный текст — на него ссылается
 * AnchorMenuComponent (ищет заголовки и раскрывает свёрнутую вкладку перед прокруткой).
 */
import {defineEmits, defineProps, ref, watch} from 'vue'
import Panel from 'primevue/panel'

const props = defineProps({
    title: {type: String, required: true},
    icon: {type: String, default: null},
    headingTag: {type: String, default: 'h1'},
    collapsed: {type: Boolean, default: true},
    loading: {type: Boolean, default: false},
    // Кнопка "Обновить принудительно" — не нужна там, где нечего обновлять отдельно от
    // первой загрузки.
    reloadable: {type: Boolean, default: true},
});

const emit = defineEmits({
    'update:collapsed': null,
    reload: null,
    expand: null,
});

// Контент монтируется один раз при первом раскрытии и дальше не размонтируется — Panel сам
// скрывает его при повторном сворачивании, поэтому состояние внутри (введённый текст и т.п.)
// не теряется.
const opened = ref(!props.collapsed);

const setCollapsed = (next) => {
    emit('update:collapsed', next);

    if (!next && !opened.value) {
        opened.value = true;
        emit('expand');
    }
};

watch(() => props.collapsed, (collapsed) => {
    if (!collapsed) opened.value = true;
});
</script>

<style scoped>
.lazy-panel-heading {
    margin: 0;
}
</style>
