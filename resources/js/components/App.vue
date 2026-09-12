<template>
    <Toast position="top-center"/>

    <header class="app-header">
        <Button icon="pi pi-bars" severity="secondary" raised v-tooltip.right="'Меню'" @click="menu.show()"/>
    </header>

    <div class="app-body">
        <div class="main-content">
            <router-view/>
        </div>
    </div>

    <AppMenuComponent/>
</template>

<script setup>
import Button from 'primevue/button'
import {menu, AppMenuComponent} from 'cmp/template/AppMenu'

</script>
