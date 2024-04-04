import AppPage from 'page/AppPage'
import ProfilePage from 'page/ProfilePage'

export default [
    {
        path: '/',
        name: 'home',
        component: AppPage,
    },
    {
        path: '/clients',
        name: 'clients',
        children: [
            {
                path: '/clients/profile',
                name: 'clientsProfile',
                component: ProfilePage,
            }
        ],
    },
];
