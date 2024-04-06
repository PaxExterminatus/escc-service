import { createStore } from 'vuex';

import AppMenuStore from 'cmp/template/AppMenu/AppMenuStore';

const store = createStore({
    modules: {
        menu: AppMenuStore,
    },
    state: {},
    mutations: {},
});

export default store
