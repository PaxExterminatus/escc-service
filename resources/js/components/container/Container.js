import {containerAPI} from './ContainerAPI';

class Container {

    constructor() {
        this.api = containerAPI;
        this.id = null
        this.client_id = null
        this.sub_id = null
        this.code = null
        this.created_at = null
        this.status = null
        this.send_date = null
        this.client_email = null
        this.email_allowed = false
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
        this.sub_id = data.sub_id
        this.code = data.code
        this.created_at = data.created_at
        this.status = data.status
        this.send_date = data.send_date
        this.client_email = data.client_email
        this.email_allowed = data.email_allowed

        return this;
    }
}

export {
    Container,
}
