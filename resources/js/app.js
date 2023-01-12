import { createApp } from 'vue'

// Libraries -----------------------------------------------------------------------------------------------------------
import PrimeVue from 'primevue/config'
import Tooltip from 'primevue/tooltip'
import ToastService from 'primevue/toastservice'
import Toast from 'primevue/toast'

// Components ----------------------------------------------------------------------------------------------------------
import App from 'cmp/App.vue'

// Options -------------------------------------------------------------------------------------------------------------
import store from 'app/store'
import router from 'app/router'

// Application ---------------------------------------------------------------------------------------------------------
const app = createApp(App)

app.use(PrimeVue)
app.use(ToastService)
app.use(router)
app.use(store)

app.component('Toast', Toast)
app.directive('tooltip', Tooltip);
app.mount("#app");
