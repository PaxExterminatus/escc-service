import axios from 'axios';

class ContainerAPI {

    routers = {
        get: (id) => `/api/container/${id}`,
        stop: (id) => `/api/container/${id}/stop`,
        start: (id) => `/api/container/${id}/start`,
        setStatus: (id, statusId) => `/api/container/${id}/status/${statusId}`,
        search: () => `/api/containers/search`,
        finance: (id) => `/api/container/${id}/finance`,
    };

    /**
     * @param {object} filters
     * @param {number} page
     * @return {Promise<axios.AxiosResponse<PaginatedResponse>>}
     */
    search(filters, page = 1)
    {
        return axios.get(this.routers.search(), {params: {...filters, page}});
    }

    /**
     * @param {string|int} id
     * @return {Promise<axios.AxiosResponse<ContainerResponseData>>}
     */
    get(id)
    {
        return axios.get(this.routers.get(id));
    }

    /**
     * @param {string|int} id
     * @return {Promise<axios.AxiosResponse<ContainerResponseData>>}
     */
    stop(id)
    {
        return axios.post(this.routers.stop(id));
    }

    /**
     * @param {string|int} id
     * @return {Promise<axios.AxiosResponse<ContainerResponseData>>}
     */
    start(id)
    {
        return axios.post(this.routers.start(id));
    }

    /**
     * @param {string|int} id
     * @param {int} statusId
     * @return {Promise<axios.AxiosResponse<ContainerResponseData>>}
     */
    setStatus(id, statusId)
    {
        return axios.post(this.routers.setStatus(id, statusId));
    }

    /**
     * @param {string|int} id
     * @return {Promise<axios.AxiosResponse<{
     *   invoiced: number, received: number, returned: number, lost: number, balance: number,
     *   postage: {fee: number, fee_client: number},
     *   items: {name: string, price: number, cost: number, discount: number}[],
     * }>>}
     */
    finance(id)
    {
        return axios.get(this.routers.finance(id));
    }
}

const containerAPI = new ContainerAPI;

export {
    containerAPI,
}
