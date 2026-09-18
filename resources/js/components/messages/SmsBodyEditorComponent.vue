<template>
    <TagAwareTextarea
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        :label="label"
        :disabled="disabled"
        :id="id"
        :client-id="clientId"
    >
        <template #footer>
            <div class="text-sm text-color-secondary">
                {{ sms.length }} / {{ sms.singleLimit }} символов
                ({{ sms.encoding }} — {{ sms.encoding === 'UCS-2' ? 'есть кириллица или др. не-латиница' : 'латиница/цифры' }}),
                {{ sms.segments }} {{ pluralize(sms.segments, 'сегмент', 'сегмента', 'сегментов') }}
            </div>
        </template>
    </TagAwareTextarea>
</template>

<script setup>
import {computed, defineEmits, defineProps} from 'vue'
import TagAwareTextarea from './TagAwareTextareaComponent.vue'
import {calculateSmsSegments} from 'utils/smsLength'
import {pluralize} from 'app/pluralize'

const props = defineProps({
    modelValue: {type: String, default: ''},
    label: {type: String, default: 'Текст SMS (плейсхолдеры вида {amount})'},
    disabled: {type: Boolean, default: false},
    id: {type: String, default: 'smsBody'},
    clientId: {type: [String, Number], default: null},
});

defineEmits({
    'update:modelValue': null,
});

const sms = computed(() => calculateSmsSegments(props.modelValue ?? ''));
</script>
