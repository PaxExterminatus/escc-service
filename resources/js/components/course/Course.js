import {courseAPI} from './CourseAPI';

class Course {

    constructor() {
        this.api = courseAPI;
        this.id = null
        this.client_id = null
        this.course_name = null
        this.status = null
        this.start_date = null
        this.next_send_date = null
    }

    /**
     *
     * @param {string|number|null} id
     * @return {Course}
     */
    static empty({id = null})
    {
        const course = new Course;
        course.id = id;
        return course;
    }

    /**
     * @param {CourseResponseData} data
     */
    fill(data)
    {
        this.id = data.sub_id
        this.client_id = data.client_id
        this.course_name = data.course_name
        this.status = data.status
        this.start_date = data.start_date
        this.next_send_date = data.next_send_date

        return this;
    }
}

export {
    Course,
}
