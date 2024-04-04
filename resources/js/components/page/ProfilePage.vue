<template>
    <h1>Profile</h1>

    <div class="mb-2">
        <Button label="Search" severity="secondary" outlined @click="search"/>
    </div>

    <Card class="w-full">
        <template #title></template>
        <template #content>

            <InputGroup class="mb-5">
                <FloatLabel>
                    <InputText v-model="client.id" inputId="clientId" :useGrouping="false" @keydown.enter="search"/>
                    <label for="clientId">ID</label>
                </FloatLabel>
            </InputGroup>

            <InputGroup class="mb-5">
                <FloatLabel>
                    <InputText v-model="client.name" inputId="clientName" :useGrouping="false"/>
                    <label for="clientName">Имя</label>
                </FloatLabel>

                <FloatLabel>
                    <InputText v-model="client.name_middle" inputId="clientNameMiddle" :useGrouping="false"/>
                    <label for="clientNameMiddle">Отчество</label>
                </FloatLabel>

                <FloatLabel>
                    <InputText v-model="client.name_last" inputId="clientNameLast" :useGrouping="false"/>
                    <label for="clientNameLast">Фамилия</label>
                </FloatLabel>
            </InputGroup>
        </template>
    </Card>
</template>

<script>
import {defineComponent} from 'vue'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import FloatLabel from 'primevue/floatlabel';
import Button from 'primevue/button'
import axios from 'axios'

export default defineComponent({
    name: 'ProfilePage',

    components: {
        Card,
        InputGroup,
        InputGroupAddon,
        Button,
        InputText,
        FloatLabel,
    },

    mounted() {
        if (this.client.id) this.search();
    },

    data() {
        return {
            client: {
                id: this.$route.params.id ?? null,
                name: null,
                name_last: null,
                name_middle: null,
            },
        };
    },

    methods: {
        search()
        {
            if (this.client.id)
            {
                axios.get(`/api/profile/${this.client.id}`)
                    .then((response) => {
                        this.client = response.data.profile;

                        if (this.client.id)
                            this.$router.push({ name: 'clientsProfile', params: { id: response.data.profile.id } })
                    })
            }
        }
    },
})
</script>
