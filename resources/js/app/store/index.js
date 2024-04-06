import { createStore } from 'vuex';
import menu from 'cmp/template/AppMenu/AppMenuStore';

const store = createStore({
    modules: {
        menu,
    },
    state: {},
    mutations: {},
});

export default store;
