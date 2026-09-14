<template>
    <Card class="w-full">
        <template #title></template>
        <template #content>
            <InputGroup>
                <Input v-model="profile().id" @enter="search" id="clientId" label="ID"/>
            </InputGroup>

            <InputGroup>
                <Input v-model="profile().name_last" id="clientNameMiddle" label="Фамилия"/>
                <Input v-model="profile().name" id="clientName" label="Имя"/>
                <Input v-model="profile().name_middle" id="clientNameMiddle" label="Отчество"/>
            </InputGroup>

            <InputGroup>
                <Input v-model="profile().birthday" :label="birthdayLabel" id="clientBirthday"/>

                <FloatLabel>
                    <Dropdown v-model="profile().sex" :options="sexes" optionLabel="name" id="clientSex"/>
                    <label for="clientSex">Sex</label>
                </FloatLabel>
            </InputGroup>

            <Fieldset legend="Контакты" class="contacts-fieldset mt-3">
                <Toolbar class="contacts-toolbar">
                    <template #start>
                        <Button label="Сохранить" icon="pi pi-save" size="small" :loading="savingCommunication" @click="saveCommunication"/>
                    </template>
                </Toolbar>

                <InputGroup>
                    <Input v-model="profile().phone" id="clientPhone" label="Телефон"/>
                    <InputGroupAddon>
                        <ToggleButton
                            class="contact-toggle"
                            v-tooltip.left="'Клиент дал согласие на отправку SMS?'"
                            :model-value="profile().sms_allowed"
                            :disabled="togglingSms"
                            onIcon="pi pi-check"
                            offIcon="pi pi-times"
                            aria-label="Согласие на SMS"
                            @update:model-value="toggleSms"
                        />
                    </InputGroupAddon>
                </InputGroup>

                <InputGroup>
                    <Input v-model="profile().email" id="clientEmail" label="Email"/>
                    <InputGroupAddon>
                        <ToggleButton
                            class="contact-toggle"
                            v-tooltip.left="'Клиент дал согласие на отправку Email?'"
                            :model-value="profile().email_allowed"
                            :disabled="togglingEmail"
                            onIcon="pi pi-check"
                            offIcon="pi pi-times"
                            aria-label="Согласие на Email"
                            @update:model-value="toggleEmail"
                        />
                    </InputGroupAddon>
                </InputGroup>
            </Fieldset>
        </template>
    </Card>
</template>

<script setup>
import {computed, defineEmits, defineProps, ref} from 'vue'
import Card from 'primevue/card'
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import Input from 'element/Input.vue'
import {Profile} from './Profile.js'
import {profileSexOptions} from './ProfileSex.js'
import {birthdayInfo} from './BirthdayInfo.js'
import {showError, showSuccess} from 'app/toast'

import Dropdown from 'primevue/dropdown'
import FloatLabel from 'primevue/floatlabel';
import Fieldset from 'primevue/fieldset';
import ToggleButton from 'primevue/togglebutton';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';

const props = defineProps({
    client: Profile,
});

/** @return {Profile} */
const profile = () => {
    return props.client;
}

const sexes = ref(profileSexOptions);

const info = computed(() => birthdayInfo(profile().birthday));
const birthdayLabel = computed(() => {
    if (!info.value) return 'Birthday';

    return `Birthday (${info.value.age}), ${info.value.countdown}`;
});

const emit = defineEmits({
    search: null,
});

const search = () => {
    emit('search');
};

const persistCommunication = () => {
    return profile().api.updateCommunication(profile().id, {
        phone: profile().phone,
        sms_allowed: profile().sms_allowed,
        email: profile().email,
        email_allowed: profile().email_allowed,
    });
};

const savingCommunication = ref(false);

const saveCommunication = () => {
    savingCommunication.value = true;

    persistCommunication()
        .then(() => {
            showSuccess('Контакты сохранены.');
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось сохранить.');
        })
        .finally(() => {
            savingCommunication.value = false;
        });
};

const togglingSms = ref(false);

const toggleSms = (value) => {
    profile().sms_allowed = value;
    togglingSms.value = true;

    persistCommunication()
        .then(() => {
            showSuccess('Согласие на SMS сохранено.');
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось сохранить.');
        })
        .finally(() => {
            togglingSms.value = false;
        });
};

const togglingEmail = ref(false);

const toggleEmail = (value) => {
    profile().email_allowed = value;
    togglingEmail.value = true;

    persistCommunication()
        .then(() => {
            showSuccess('Согласие на Email сохранено.');
        })
        .catch((error) => {
            showError(error.response?.data?.message ?? 'Не удалось сохранить.');
        })
        .finally(() => {
            togglingEmail.value = false;
        });
};
</script>

<style scoped>
.contacts-fieldset :deep(.p-fieldset-content) {
    padding: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.contacts-toolbar {
    border: none;
    padding: 0;
    background: transparent;
}

.contact-toggle :deep(.p-button) {
    padding: 0.35rem 0.6rem;
    min-width: 0;
    font-size: 0.85rem;
}

.contact-toggle :deep(.p-button-icon) {
    font-size: 0.85rem;
}
</style>
