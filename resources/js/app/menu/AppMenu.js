import {MenuItem} from './Menu';

export const appMenu = [
    MenuItem({
        label: 'Home',
        icon: 'pi pi-home',
        route: '/',
    }),
    MenuItem({
        label: 'Клиенты',
        icon: 'pi pi-users',
        items: [
            MenuItem({
                label: 'Профиль',
                icon: 'pi pi-user',
                route: '/clients/profile',
            }),
        ],
    }),
    MenuItem({
        label: 'Docs',
        icon: 'pi pi-file',
        items: [
            MenuItem({
                label: 'API',
                icon: 'pi pi-code',
                url: '/docs/api/',
                target: 'blank',
            }),
        ],
    }),
];
