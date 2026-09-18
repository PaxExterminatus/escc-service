<template>
    <div class="tag-grid">
        <div v-for="group in groups" :key="group.category" class="tag-card">
            <div class="tag-card-header">{{ group.label }}</div>
            <div class="tag-card-body">
                <a
                    v-for="tag in group.tags"
                    :key="tag.code"
                    href="#"
                    class="tag-chip"
                    v-tooltip.top="tag.description + ' (' + tag.scope_label + ')'"
                    @click.prevent="$emit('select', tag)"
                >{{ '{' + tag.code + '}' }}</a>
            </div>
        </div>
    </div>
</template>

<script setup>
import {computed, defineEmits, defineProps} from 'vue'

const props = defineProps({
    tags: {type: Array, default: () => []},
});

defineEmits({
    select: null,
});

const groups = computed(() => {
    const map = new Map();

    for (const tag of props.tags) {
        if (!map.has(tag.category)) {
            map.set(tag.category, {category: tag.category, label: tag.category_label, tags: []});
        }

        map.get(tag.category).tags.push(tag);
    }

    return [...map.values()];
});
</script>

<style scoped>
/* Раньше категории были вкладками аккордеона, развёрнутыми одна под другой — список тегов
   растягивался по вертикали. Карточки в сетке кладут категории рядом друг с другом: то же
   количество тегов на заметно меньшей высоте. */
.tag-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 0.75rem;
}

.tag-card {
    border: 1px solid var(--surface-border, rgba(255, 255, 255, 0.1));
    border-radius: 8px;
    padding: 0.6rem 0.75rem;
}

.tag-card-header {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-color-secondary, rgba(255, 255, 255, 0.6));
    margin-bottom: 0.5rem;
    white-space: nowrap;
}

.tag-card-body {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.tag-chip {
    display: inline-block;
    font-family: ui-monospace, Consolas, monospace;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    background: var(--surface-100, rgba(255, 255, 255, 0.06));
    color: var(--text-color);
    text-decoration: none;
    white-space: nowrap;
}

.tag-chip:hover {
    background: var(--primary-color);
    color: var(--primary-color-text);
}
</style>
