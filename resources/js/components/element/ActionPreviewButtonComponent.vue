<template>
    <InputGroup class="action-preview-group">
        <Button
            :label="label"
            :icon="icon"
            outlined
            :loading="loading"
            :disabled="disabled"
            class="action-preview-main"
            v-tooltip.top="tooltip"
            @click="onMainClick"
        />

        <Button
            v-if="showPreview"
            icon="pi pi-eye"
            outlined
            :loading="previewLoading"
            class="action-preview-eye"
            v-tooltip.top="previewTooltip"
            @click="$emit('preview')"
        />
    </InputGroup>
</template>

<script setup>
import {defineEmits, defineProps} from 'vue'
import InputGroup from 'primevue/inputgroup'
import Button from 'primevue/button'

const props = defineProps({
    label: {type: String, required: true},
    icon: {type: String, default: null},
    href: {type: String, default: null},
    loading: {type: Boolean, default: false},
    disabled: {type: Boolean, default: false},
    tooltip: {type: String, default: null},
    showPreview: {type: Boolean, default: true},
    previewLoading: {type: Boolean, default: false},
    previewTooltip: {type: String, default: 'Просмотр'},
});

const emit = defineEmits({
    click: null,
    preview: null,
});

const onMainClick = () => {
    if (props.href) {
        window.open(props.href, '_blank');
    }

    emit('click');
};
</script>

<style scoped>
.action-preview-group {
    display: inline-flex;
    width: fit-content;
    align-self: flex-start;
}

.action-preview-main :deep(.p-button-label) {
    flex: 0 1 auto;
}

.action-preview-main {
    border-radius: 6px 0 0 6px !important;
}

.action-preview-eye {
    border-radius: 0 6px 6px 0 !important;
}
</style>
