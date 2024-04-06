const state = () => ({
    visible: true,
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
    /**
     * @param {AppMenuState} state
     * @return boolean
     */
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
