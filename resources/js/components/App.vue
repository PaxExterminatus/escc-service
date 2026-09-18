<template>
    <Toast position="top-center"/>

    <header class="app-header">
        <Button icon="pi pi-bars" severity="secondary" raised v-tooltip.right="'Меню'" @click="menu.show()"/>
        <div id="app-header-end"></div>
    </header>

    <div class="app-body">
        <div class="main-content">
            <router-view/>
        </div>
    </div>

    <footer class="app-footer"></footer>

    <AppMenuComponent/>
</template>

<script setup>
import Button from 'primevue/button'
import {menu, AppMenuComponent} from 'cmp/template/AppMenu'

</script>
