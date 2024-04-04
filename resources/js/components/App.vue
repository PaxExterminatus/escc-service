<template>
    <div class="main-menu">
        <PanelMenu :model="appMenu" multiple class="w-full md:w-20rem"/>
    </div>

    <div class="main-content">
        <router-view/>
    </div>
</template>

<script>
import PanelMenu from 'primevue/panelmenu'
import {appMenu} from 'menu/AppMenu'

export default {
    components: {
        PanelMenu,
    },

    data() {
        return {
            /**
             * @var MenuItem[]
             */
            appMenu,
        };
    },

    computed: {

    },
}
</script>
