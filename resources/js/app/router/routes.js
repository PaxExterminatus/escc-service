// import MemberSection from 'cmp/page/member/MemberSection'
// import MemberHomeContent from 'cmp/page/member/MemberHomeContent'
import {GuestSection, OperatorSection} from 'cmp/page-sections';
import {OperatorBase, MessagesSending} from 'cmp/page-content';


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
                path: '/messages/sending',
                name: 'messages-sending',
                component: () => import(/* webpackChunkName: "chunk.page-content.messages.sending" */ 'cmp/page-content/MessagesSending'),
            },
        ],
    },
];