import { createStore } from 'vuex';

import AppMenuStore from 'cmp/template/AppMenu/AppMenuStore';
import {menu} from 'cmp/template/AppMenu';

const store = createStore({
    modules: {
        menu: AppMenuStore,
    },
    state: {},
    mutations: {},
});

export default store

export {
    menu
}
