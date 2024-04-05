<template>
    <div class="main-menu">
        <MainMenu/>
    </div>

    <div class="main-content">
        <router-view/>
    </div>
</template>

<script setup>
import MainMenu from 'cmp/template/MainMenu'

</script>
