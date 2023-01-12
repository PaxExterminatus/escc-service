// import MemberSection from 'cmp/page/member/MemberSection'
// import MemberHomeContent from 'cmp/page/member/MemberHomeContent'
import {GuestSection, OperatorSection} from 'cmp/page-sections';
import {OperatorBase} from "cmp/page-content";

export default [
    {
        path: '/',
        name: 'home',
        component: OperatorSection,
        children: [
            {
                path: '/',
                name: 'base',
                component: OperatorBase,
            },
            {
                path: '/messages',
                name: 'messages',
                component: () => import(/* webpackChunkName: "chunk.page.member.courses" */ 'page/member/CoursesContent'),
            },
        ],
    },
    // {
    //     path: '/auth',
    //     name: 'auth',
    //     component: GuestSection,
    // },
];
