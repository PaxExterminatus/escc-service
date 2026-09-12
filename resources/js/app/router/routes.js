import AppPage from 'page/AppPage'
import ProfilePage from 'page/ProfilePage'
import ContainerPage from 'page/ContainerPage';
import TemplatesPage from 'page/TemplatesPage';
import DailyMessagingPage from 'page/DailyMessagingPage';

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
                path: '/clients/profile/:id?',
                name: 'clientsProfile',
                component: ProfilePage,
            }
        ],
    },

    {
        path: '/container',
        name: 'container',
        children: [
            {
                path: '/container/:id?',
                name: 'containerShow',
                component: ContainerPage,
            }
        ],
    },

    {
        path: '/messages',
        name: 'messages',
        children: [
            {
                path: '/messages/templates',
                name: 'messagesTemplates',
                component: TemplatesPage,
            },
            {
                path: '/messages/daily',
                name: 'messagesDaily',
                component: DailyMessagingPage,
            },
        ],
    },
];
