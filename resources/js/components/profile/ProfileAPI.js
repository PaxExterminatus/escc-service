import axios from 'axios';

class ProfileAPI {

    /**
     * @param {int} id
     * @return {Promise<axios.AxiosResponse<ProfileResponseData>>}
     */
    get(id)
    {
        return axios.get(`/api/profile/${id}`)
            .then((response) => {
                return response;
            });
    }
}

const profileAPI = new ProfileAPI;

export {
    profileAPI,
}
