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
        ],
    }),
    MenuItem({
        key: 3,
        label: 'Docs',
        icon: 'pi pi-file',
        items: [
            MenuItem({
                label: 'API',
                icon: 'pi pi-code',
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
};
