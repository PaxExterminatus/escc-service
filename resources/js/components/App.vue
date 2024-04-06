<template>
    <header class="app-header">
        <Button icon="pi pi-bars" severity="secondary" raised @click="menu.show()"/>
        <AppMenu/>
    </header>

    <div class="app-body">

        <div class="main-content">
            <router-view/>
        </div>
    </div>
</template>

<script setup>
import AppMenu from 'cmp/template/AppMenu/AppMenuComponent'
import {menu} from 'app/lib'

import Button from 'primevue/button'
</script>
