<template>
    <header class="app-header">
        <Button icon="pi pi-bars" severity="secondary" raised @click="menu.show()"/>
    </header>

    <div class="app-body">
        <Sidebar v-model:visible="menu.visible" class="main-menu-bar">
            <AppMenu/>
        </Sidebar>

        <div class="main-content">
            <router-view/>
        </div>
    </div>
</template>

<script setup>
import {menu, AppMenu} from 'cmp/template/AppMenu'

import Sidebar from 'primevue/sidebar';
import Button from 'primevue/button'
</script>
