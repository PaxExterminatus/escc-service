<template>
    <div class="flex justify-content-center align-items-center" :class="small ? 'p-3' : 'p-4'">
        <ProgressSpinner :style="`width: ${size}px; height: ${size}px`" strokeWidth="4"/>
    </div>
</template>

<script setup>
import {computed, defineProps} from 'vue'
import ProgressSpinner from 'primevue/progressspinner'

const props = defineProps({
    // Компактный вариант — для панелей внутри карточек, обычный — для содержимого диалогов
    small: {type: Boolean, default: false},
});

const size = computed(() => props.small ? 30 : 40);
</script>
