<template>
    <h1>Profile</h1>

    <Card class="w-full">
        <template #title></template>
        <template #content>
            <div class="md:mb-2">
                <Button label="Search" severity="secondary" outlined />
            </div>

            <InputGroup>
                <InputGroupAddon>
                    <i class="pi pi-user"></i>
                </InputGroupAddon>
                <InputNumber v-model="client.id" inputId="clientId" :useGrouping="false" placeholder="ID"/>
            </InputGroup>

        </template>
    </Card>
</template>

<script>
import {defineComponent} from 'vue'
import Card from 'primevue/card'
import InputNumber from 'primevue/inputnumber'
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import Button from 'primevue/button'

export default defineComponent({
    name: 'ProfilePage',
    components: {
        Card,
        InputNumber,
        InputGroup,
        InputGroupAddon,
        Button,
    },
    data() {
        return {
            client: {
                id: null,
            },
        };
    },
})
</script>
