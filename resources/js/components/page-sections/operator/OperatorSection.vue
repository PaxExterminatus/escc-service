<template>
    <div class="page-section">

        <nav class="section-menu">
            <PanelMenu :model="operatorMenu"></PanelMenu>
        </nav>

        <main class="content-main">
            <router-view/>
        </main>
    </div>
</template>

<script>
import PanelMenu from 'primevue/panelmenu';
import {operatorMenu} from './operator-menu'

export default {
    name: 'OperatorSection',

    components: {
        PanelMenu,
    },

    data() {
        return {
            operatorMenu,
        }
    },
}
</script>
