import axios from 'axios';

class CourseAPI {

    routers = {
        courses: (clientId) => `/api/clients/${clientId}/courses`,
        containers: (subId) => `/api/courses/${subId}/containers`,
    };

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
