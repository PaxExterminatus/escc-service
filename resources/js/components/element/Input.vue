<template>
    <FloatLabel>
        <InputText v-model="modelValue" :inputId="props.id" :useGrouping="false" @keydown.enter="enter"/>
        <label :for="props.id">{{props.label}}</label>
    </FloatLabel>
</template>

<script setup>

import {defineProps, defineEmits, defineModel} from 'vue'
import InputText from 'primevue/inputtext'
import FloatLabel from 'primevue/floatlabel'

const props = defineProps({
    id: String,
    label: String,
});

const modelValue = defineModel();

const emit = defineEmits({
    enter: null,
});

const enter = () => {
    emit('enter');
};

</script>
