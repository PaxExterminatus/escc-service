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
        // Раньше здесь были только passwordPrompt/emailPrompt — этого хватало, пока в
        // приложении не использовался Calendar: он читает dayNames/monthNames и падает
        // (TypeError: Cannot read properties of undefined), если их нет вовсе, а не
        // подставляет свои дефолты — PrimeVue не мёржит locale с англ. дефолтом сам.
        startsWith: 'Начинается с',
        contains: 'Содержит',
        notContains: 'Не содержит',
        endsWith: 'Заканчивается на',
        equals: 'Равно',
        notEquals: 'Не равно',
        noFilter: 'Без фильтра',
        lt: 'Меньше чем',
        lte: 'Меньше или равно',
        gt: 'Больше чем',
        gte: 'Больше или равно',
        dateIs: 'Дата равна',
        dateIsNot: 'Дата не равна',
        dateBefore: 'Дата до',
        dateAfter: 'Дата после',
        clear: 'Очистить',
        apply: 'Применить',
        matchAll: 'Все совпадения',
        matchAny: 'Любое совпадение',
        addRule: 'Добавить правило',
        removeRule: 'Удалить правило',
        accept: 'Да',
        reject: 'Нет',
        choose: 'Выбрать',
        upload: 'Загрузить',
        cancel: 'Отмена',
        dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        dayNamesShort: ['Вск', 'Пнд', 'Втр', 'Срд', 'Чтв', 'Птн', 'Суб'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
        today: 'Сегодня',
        weekHeader: 'Нед',
        firstDayOfWeek: 1,
        dateFormat: 'dd.mm.yy',
        weak: 'Слабый',
        medium: 'Средний',
        strong: 'Сильный',
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
