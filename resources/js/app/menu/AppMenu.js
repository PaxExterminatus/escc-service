import {MenuItem} from './Menu';

export const appMenu = [
    MenuItem({
        label: 'Клиенты',
        icon: 'pi pi-users',
        items: [
            MenuItem({
                label: 'Профиль',
                icon: 'pi pi-user',
            }),
        ],
    }),
    MenuItem({
        label: 'Docs',
        icon: 'pi pi-file',
        items: [
            MenuItem({
                label: 'API',
                url: '/docs/api/',
                target: 'blank',
            }),
        ],
    }),
];
