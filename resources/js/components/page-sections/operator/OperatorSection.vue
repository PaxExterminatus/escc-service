<template>
    <div class="page-section">

        <nav class="section-menu">
<!--            <Sidebar v-model:visible="operatorMenuVisible"></Sidebar>-->
            <Menu :model="operatorMenu"/>
        </nav>

        <main class="content-main">
            <router-view/>
        </main>
    </div>
</template>

<script>
import Sidebar from 'primevue/sidebar';
import Menu from 'primevue/menu';
import {operatorMenu} from './operator-menu'

export default {
    name: 'OperatorSection',

    components: {
        Menu,
        Sidebar,
    },

    data() {
        return {
            operatorMenu,
            operatorMenuVisible: true,
        }
    },
}
</script>
