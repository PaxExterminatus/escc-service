import AppPage from 'page/AppPage'
import ProfilePage from 'page/ProfilePage'
import ContainerPage from 'page/ContainerPage';
import TemplatesPage from 'page/TemplatesPage';
import DailyMessagingPage from 'page/DailyMessagingPage';
import InvoiceDailyPage from 'page/InvoiceDailyPage';
import TagsPage from 'page/TagsPage';

export default [
    {
        path: '/',
        name: 'home',
        component: AppPage,
    },
    {
        path: '/tags',
        name: 'tags',
        component: TagsPage,
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
            },
            {
                path: '/container/invoices/daily',
                name: 'containerInvoicesDaily',
                component: InvoiceDailyPage,
            },
        ],
    },

    {
        path: '/messages',
        name: 'messages',
        children: [
            {
                path: '/messages/templates/:code?',
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
