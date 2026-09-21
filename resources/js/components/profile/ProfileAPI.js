import axios from 'axios';

class ProfileAPI {

    routers = {
        get: (id) => `/api/profile/${id}`,
        updateCommunication: (id) => `/api/profile/${id}/communication`,
        search: () => `/api/clients/search`,
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
     * @return {Promise<axios.AxiosResponse<ProfileResponseData>>}
     */
    get(id)
    {
        return axios.get(this.routers.get(id))
            .then((response) =>
            {
                /** @type {ProfileResponseData}*/
                const data = response.data;
                return response;
            });
    }

    /**
     * @param {string|int} id
     * @param {{phone: string|null, sms_allowed: boolean, email: string|null, email_allowed: boolean}} payload
     * @return {Promise<axios.AxiosResponse<ProfileResponseData>>}
     */
    updateCommunication(id, payload)
    {
        return axios.put(this.routers.updateCommunication(id), payload);
    }
}

const profileAPI = new ProfileAPI;

export {
    profileAPI,
}
