<template>
    <div class="flex flex-column gap-3">
        <div class="flex flex-column gap-2">
            <FloatLabel>
                <Textarea
                    :model-value="modelValue"
                    @update:model-value="$emit('update:modelValue', $event)"
                    :id="id"
                    rows="5"
                    autoResize
                    class="w-full"
                    :disabled="disabled"
                />
                <label :for="id">{{ label }}</label>
            </FloatLabel>

            <!-- Чем канал отличается: счётчик сегментов у SMS, предпросмотр письма у Email -->
            <slot name="footer"/>
        </div>

        <TagInsertPanel :client-id="clientId" @insert="insertValue"/>
    </div>
</template>

<script setup>
import {defineEmits, defineProps} from 'vue'
import Textarea from 'primevue/textarea'
import FloatLabel from 'primevue/floatlabel'
import {TagInsertPanel} from 'cmp/tags'
import {appendTag} from 'utils/tag'

const props = defineProps({
    modelValue: {type: String, default: ''},
    label: {type: String, default: ''},
    disabled: {type: Boolean, default: false},
    id: {type: String, default: 'messageBody'},
    clientId: {type: [String, Number], default: null},
});

const emit = defineEmits({
    'update:modelValue': null,
});

const insertValue = (value) => {
    emit('update:modelValue', appendTag(props.modelValue, value));
};
</script>
