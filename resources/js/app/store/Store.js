import store from './index'

class Store {
    /**
     * @param {UserResponse} userData
     */
    static setUserMutation(userData) {
        store.commit('auth/setUser', userData);
    }
}

export {
    Store,
}
