import store from 'Domain/App/store'

class AppMenuStoreAdapter
{
    constructor(store)
    {
        this.$store = store;
    }

    get visible() {
        return this.$store.state.menu.visible;
    }

    set visible(status) {
        return this.$store.state.menu.visible = status;
    }

    show() {
        this.$store.commit('menu/show');
    }

    hide() {
        this.$store.commit('menu/hide');
    }
}

const menu = new AppMenuStoreAdapter(store);

export default menu
