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
        ],
    }),

    MenuItem({
        key: 4,
        label: 'Рассылки',
        icon: 'pi pi-envelope',
        items: [
            MenuItem({
                key: 41,
                label: 'Шаблоны сообщений',
                icon: 'pi pi-file-edit',
                route: '/messages/templates',
                command () {
                    menu.hide()
                },
            }),
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
};
