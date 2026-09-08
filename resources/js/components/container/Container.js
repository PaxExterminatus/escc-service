import {containerAPI} from './ContainerAPI';

class Container {

    constructor() {
        this.api = containerAPI;
        this.id = null
        this.client_id = null
        this.code = null
        this.created_at = null
        this.status = null
    }

    /**
     *
     * @param {string|number|null} id
     * @return {Container}
     */
    static empty({id = null})
    {
        const container = new Container;
        container.id = id;
        return container;
    }

    /**
     * @param {ContainerResponseData} data
     */
    fill(data)
    {
        this.id = data.id
        this.client_id = data.client_id
        this.code = data.code
        this.created_at = data.created_at
        this.status = data.status

        return this;
    }
}

export {
    Container,
}
