<template>
    <div class="main-menu">

        <MainMenu/>

    </div>

    <div class="main-content">
        <router-view/>
    </div>
</template>

<script>
import MainMenu from 'cmp/template/MainMenu'

export default {
    components: {
        MainMenu,
    },

    methods: {
        expand()
        {
            for (let node of items.value) {
                expandNode(node);
            }

            expandedKeys.value = {...expandedKeys.value};
        }
    },
}
</script>
