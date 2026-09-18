import axios from 'axios';

class TagAPI {

    routers = {
        index: () => `/api/tags`,
        forContainer: (containerId) => `/api/containers/${containerId}/tags`,
    };

    /**
     * @return {Promise<axios.AxiosResponse<{tags: TagDefinitionRow[]}>>}
     */
    index()
    {
        return axios.get(this.routers.index());
    }

    /**
     * Готовые значения тегов уровня "Счёт" для конкретного контейнера.
     *
     * @param {string|int} containerId
     * @return {Promise<axios.AxiosResponse<{tags: Object<string, string>}>>}
     */
    forContainer(containerId)
    {
        return axios.get(this.routers.forContainer(containerId));
    }
}

const tagAPI = new TagAPI;

export {
    tagAPI,
}
