const state = () => ({
    visible: false,
});

const mutations = {
    show(state) {
        state.visible = true;
    },
    hide(state) {
        state.visible = false;
    },
}

const getters = {
    visible(state) {
        return state.visible;
    },
}

export default {
    namespaced: true,
    state,
    mutations,
    getters,
}
