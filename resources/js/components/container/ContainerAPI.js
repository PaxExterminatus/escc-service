import axios from 'axios';

class ContainerAPI {

    routers = {
        get: (id) => `/api/container/${id}`,
        stop: (id) => `/api/container/${id}/stop`,
        start: (id) => `/api/container/${id}/start`,
        setStatus: (id, statusId) => `/api/container/${id}/status/${statusId}`,
    };

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
}

const containerAPI = new ContainerAPI;

export {
    containerAPI,
}
