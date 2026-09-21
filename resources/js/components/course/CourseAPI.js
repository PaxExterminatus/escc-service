import axios from 'axios';

class CourseAPI {

    routers = {
        get: (subId) => `/api/courses/${subId}`,
        courses: (clientId) => `/api/clients/${clientId}/courses`,
        containers: (subId) => `/api/courses/${subId}/containers`,
        search: () => `/api/courses/search`,
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
     * Один курс — для страницы курса
     *
     * @param {string|int} subId
     * @return {Promise<axios.AxiosResponse<{course: CourseResponseData}>>}
     */
    get(subId)
    {
        return axios.get(this.routers.get(subId));
    }

    /**
     * @param {string|int} clientId
     * @return {Promise<axios.AxiosResponse<{courses: CourseRow[]}>>}
     */
    courses(clientId)
    {
        return axios.get(this.routers.courses(clientId));
    }

    /**
     * @param {string|int} subId
     * @return {Promise<axios.AxiosResponse<{containers: CourseContainerRow[]}>>}
     */
    containers(subId)
    {
        return axios.get(this.routers.containers(subId));
    }
}

const courseAPI = new CourseAPI;

export {
    courseAPI,
}
