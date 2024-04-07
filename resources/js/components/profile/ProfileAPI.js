import axios from 'axios';

class ProfileAPI {

    routers = {
        get: (id) => `/api/profile/${id}`
    };

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
                return response.data;
            });
    }
}

const profileAPI = new ProfileAPI;

export {
    profileAPI,
}
