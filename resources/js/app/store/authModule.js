import {CourseData} from "api/structures/CourseData";

const state = () => ({
    user: null,
    client: null,
});

const mutations = {
    /**
     * @param {AuthState} store
     * @param {UserResponseData} userData
     */
    setUser (store, userData) {
        store.user = userData.user;
        store.client = userData.client;

        if (userData.client && userData.client.courses)
        {
            const courses = userData.client.courses.map((courseData) => new CourseData(courseData))
            const active = courses.filter(course => course.state === 'active');
            const done = courses.filter(course => course.state === 'done');
            const stop = courses.filter(course => course.state === 'stop');
            store.client.courses = [
                ...active,
                ...done,
                ...stop,
            ];
        }
    },
}

export default {
    namespaced: true,
    state,
    mutations,
};
