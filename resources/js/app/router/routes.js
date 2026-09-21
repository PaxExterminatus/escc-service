import AppPage from 'page/AppPage'
import ProfilePage from 'page/ProfilePage'
import ClientsPage from 'page/ClientsPage'
import ContainerPage from 'page/ContainerPage';
import ContainersPage from 'page/ContainersPage';
import CoursePage from 'page/CoursePage';
import CoursesPage from 'page/CoursesPage';
import TemplatesPage from 'page/TemplatesPage';
import DailyMessagingPage from 'page/DailyMessagingPage';
import InvoiceDailyPage from 'page/InvoiceDailyPage';
import TagsPage from 'page/TagsPage';
import SchedulerPage from 'page/SchedulerPage';

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

    // Плоские маршруты без вложенности: ни один из компонентов страниц не рендерит свой
    // <router-view/>, поэтому "children" здесь были бы чистым неймспейсом без эффекта — Vue
    // Router прекрасно матчит /clients и /clients/profile/:id? как два независимых пути.
    {
        path: '/clients',
        name: 'clients',
        component: ClientsPage,
    },
    {
        path: '/clients/profile/:id?',
        name: 'clientsProfile',
        component: ProfilePage,
    },

    {
        path: '/containers',
        name: 'containers',
        component: ContainersPage,
    },
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

    {
        path: '/courses',
        name: 'courses',
        component: CoursesPage,
    },
    {
        path: '/course/:id?',
        name: 'courseShow',
        component: CoursePage,
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

    {
        path: '/scheduler',
        name: 'scheduler',
        component: SchedulerPage,
    },
];
