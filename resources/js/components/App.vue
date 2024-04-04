<template>
    <div id="app" class="application">

        <MegaMenu :model="appMenu" orientation="vertical"/>
        <router-view/>
    </div>
</template>

<script>
import MegaMenu from 'primevue/megamenu'
import {appMenu} from 'menu/AppMenu';

export default {
    components: {
        MegaMenu,
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
