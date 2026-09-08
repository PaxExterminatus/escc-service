import { createApp } from 'vue'
import axios from 'axios'

// Libraries -----------------------------------------------------------------------------------------------------------
import PrimeVue from 'primevue/config'
import Tooltip from 'primevue/tooltip'
import ToastService from 'primevue/toastservice'
import Toast from 'primevue/toast'

// Components ----------------------------------------------------------------------------------------------------------
import App from 'cmp/App.vue'

// Options -------------------------------------------------------------------------------------------------------------
import router from 'app/router'
import store from 'app/store'
import {showError} from 'app/toast'

// HTTP ----------------------------------------------------------------------------------------------------------------
// Универсальный обработчик "не найдено": все компоненты дёргают общий axios напрямую
// (своего клиента с перехватчиками, как в escc-cabinet, здесь нет), поэтому вешаем
// перехватчик на сам axios — покрывает все запросы разом.
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 404) {
            showError(error.response?.data?.message || 'Запрашиваемые данные не найдены');
        }

        return Promise.reject(error);
    },
)

// Application ---------------------------------------------------------------------------------------------------------
const app = createApp(App)

app.config.devtools = true

app.use(PrimeVue, {
    ripple: false,
    zIndex: {
        modal: 1100,        //dialog, sidebar
        overlay: 1000,      //dropdown, overlay panel
        menu: 1000,         //overlay menus
        tooltip: 1100       //tooltip
    },
    locale: {
        passwordPrompt: 'Введите пароль',
        emailPrompt: 'Введите адрес электронной почты',
    },
})

app.use(ToastService)
app.use(router)
app.use(store)

app.component('Toast', Toast)

app.directive('tooltip', Tooltip);

app.mount("#app");
