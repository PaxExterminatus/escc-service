import axios from 'axios';

class SchedulerAPI {

    routers = {
        tasks: () => `/api/scheduler/tasks`,
        task: (id) => `/api/scheduler/tasks/${id}`,
    };

    /**
     * @return {Promise<axios.AxiosResponse<{tasks: {task_id: number, label: string, command: string, run_time: string, is_enabled: boolean}[]}>>}
     */
    index()
    {
        return axios.get(this.routers.tasks());
    }

    /**
     * @param {number} taskId
     * @param {{run_time: string, is_enabled: boolean}} payload
     * @return {Promise<axios.AxiosResponse<{status: string}>>}
     */
    update(taskId, payload)
    {
        return axios.put(this.routers.task(taskId), payload);
    }
}

const schedulerAPI = new SchedulerAPI;

export {
    schedulerAPI,
}
