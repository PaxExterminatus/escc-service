<script setup>
    import {ref} from 'vue'
    import PanelMenu from 'primevue/panelmenu'
    import {appMenu, defaultExpandedKeys} from 'menu/AppMenu'

    const expandedKeys = ref(defaultExpandedKeys)
</script>

<template>
    <PanelMenu v-model:expandedKeys="expandedKeys" :model="appMenu" multiple class="w-full md:w-20rem">
        <template #item="{ item }">
            <router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
                <a class="flex align-items-center cursor-pointer text-color px-3 py-2" :href="href" @click="navigate">
                    <span :class="item.icon" />
                    <span class="ml-2 text-color">{{ item.label }}</span>
                </a>
            </router-link>

            <a v-else class="flex align-items-center cursor-pointer text-color px-3 py-2" :href="item.url" :target="item.target">
                <span :class="item.icon" />
                <span class="ml-2">{{ item.label }}</span>
                <span v-if="item.items" class="pi pi-angle-down text-primary ml-auto" />
            </a>
        </template>
    </PanelMenu>
</template>

