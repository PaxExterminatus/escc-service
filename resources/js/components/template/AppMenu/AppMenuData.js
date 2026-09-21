import {MenuItem} from 'menu/Menu';
import menu from './AppMenuStoreAdapter'

export const appMenuData = [
    MenuItem({
        key: 1,
        label: 'Home',
        icon: 'pi pi-home',
        route: '/',
        command () {
            menu.hide()
        },
    }),

    MenuItem({
        key: 2,
        label: 'Клиенты',
        icon: 'pi pi-users',
        items: [
            MenuItem({
                key: 21,
                label: 'Профиль',
                icon: 'pi pi-user',
                route: '/clients/profile',
                command () {
                    menu.hide()
                },
            }),
            MenuItem({
                key: 22,
                label: 'Контейнер',
                icon: 'pi pi-box',
                route: '/container',
                command () {
                    menu.hide()
                },
            }),
            MenuItem({
                key: 23,
                label: 'Курс',
                icon: 'pi pi-book',
                route: '/course',
                command () {
                    menu.hide()
                },
            }),
        ],
    }),

    MenuItem({
        key: 8,
        label: 'Поиск',
        icon: 'pi pi-search',
        items: [
            MenuItem({
                key: 81,
                label: 'Клиенты',
                icon: 'pi pi-users',
                route: '/clients',
                command () {
                    menu.hide()
                },
            }),
            MenuItem({
                key: 82,
                label: 'Контейнеры',
                icon: 'pi pi-box',
                route: '/containers',
                command () {
                    menu.hide()
                },
            }),
            MenuItem({
                key: 83,
                label: 'Курсы',
                icon: 'pi pi-book',
                route: '/courses',
                command () {
                    menu.hide()
                },
            }),
        ],
    }),

    MenuItem({
        key: 4,
        label: 'Рассылки',
        icon: 'pi pi-envelope',
        items: [
            MenuItem({
                key: 42,
                label: 'Массовые рассылки',
                icon: 'pi pi-send',
                route: '/messages/daily',
                command () {
                    menu.hide()
                },
            }),
        ],
    }),

    MenuItem({
        key: 5,
        label: 'Центр печати',
        icon: 'pi pi-print',
        items: [
            MenuItem({
                key: 51,
                label: 'Печать счетов за день',
                icon: 'pi pi-file-pdf',
                route: '/container/invoices/daily',
                command () {
                    menu.hide()
                },
            }),
        ],
    }),

    MenuItem({
        key: 6,
        label: 'Шаблоны',
        icon: 'pi pi-file-edit',
        items: [
            MenuItem({
                key: 61,
                label: 'Редактор шаблонов',
                icon: 'pi pi-file-edit',
                route: '/messages/templates',
                command () {
                    menu.hide()
                },
            }),
            MenuItem({
                key: 62,
                label: 'Теги Данных',
                icon: 'pi pi-tags',
                route: '/tags',
                command () {
                    menu.hide()
                },
            }),
        ],
    }),

    MenuItem({
        key: 7,
        label: 'Планировщик',
        icon: 'pi pi-clock',
        route: '/scheduler',
        command () {
            menu.hide()
        },
    }),

    MenuItem({
        key: 3,
        label: 'Документация',
        icon: 'pi pi-file',
        items: [
            MenuItem({
                label: 'Руководства',
                icon: 'pi pi-book',
                url: '/docs/guides/',
                target: 'blank',
                command () {
                    menu.hide()
                },
            }),
            MenuItem({
                label: 'Для программистов',
                icon: 'pi pi-code',
                url: '/docs/programmers/',
                target: 'blank',
                command () {
                    menu.hide()
                },
            }),
            MenuItem({
                label: 'Для операторов',
                icon: 'pi pi-desktop',
                url: '/docs/operators/',
                target: 'blank',
                command () {
                    menu.hide()
                },
            }),
            MenuItem({
                label: 'API',
                icon: 'pi pi-sitemap',
                url: '/docs/api/',
                target: 'blank',
                command () {
                    menu.hide()
                },
            }),
        ],
    }),
];

export const defaultExpandedKeys = {
    2: true,
    3: false,
    4: false,
    5: false,
    6: false,
};
