import axios from 'axios';

class MessagingAPI {

    routers = {
        recipient: (clientId) => `/api/messages/recipient/${clientId}`,
        send: () => `/api/messages/send`,
        daily: (type) => `/api/messages/daily/${type}`,
        dailySend: (type) => `/api/messages/daily/${type}/send`,
        dailyTxt: (type) => `/api/messages/daily/${type}/txt`,
        queue: () => `/api/messages/queue`,
        queueItem: (id) => `/api/messages/queue/${id}`,
        history: (clientId) => `/api/messages/history/${clientId}`,
    };

    /**
     * @param {string|number} clientId
     * @return {Promise<axios.AxiosResponse<{messages: {id: number, type: string, address: string, body: string, status: string, date: string}[]}>>}
     */
    history(clientId)
    {
        return axios.get(this.routers.history(clientId));
    }

    /**
     * @param {string|number} clientId
     * @return {Promise<axios.AxiosResponse<{channels: {code: string, label: string, address: (string|null), allowed: boolean}[]}>>}
     */
    recipient(clientId)
    {
        return axios.get(this.routers.recipient(clientId));
    }

    /**
     * @param {{client_id: (string|number), channel: string, template_id: (number|null), body: (string|null), params: object}} payload
     * @return {Promise<axios.AxiosResponse<{emsg_id: number, response: object}>>}
     */
    send(payload)
    {
        return axios.post(this.routers.send(), payload);
    }

    /**
     * @param {string} type 'sms'|'email'
     * @param {{from?: string|null, to?: string|null}} range оба Y-m-d, включительно; ни одного — без ограничения по дате
     * @return {Promise<axios.AxiosResponse<{messages: object[]}>>}
     */
    daily(type, range = {})
    {
        return axios.get(this.routers.daily(type), {params: this.cleanRange(range)});
    }

    /**
     * @param {string} type 'sms'|'email'
     * @return {Promise<axios.AxiosResponse<object>>}
     */
    dailySend(type)
    {
        return axios.get(this.routers.dailySend(type));
    }

    /**
     * @param {string} type 'sms'|'email'
     * @param {{from?: string|null, to?: string|null}} range
     * @return {string}
     */
    dailyTxtUrl(type, range = {})
    {
        const query = new URLSearchParams(this.cleanRange(range)).toString();

        return this.routers.dailyTxt(type) + (query ? `?${query}` : '');
    }

    /** Отбрасывает null/undefined — иначе axios/URLSearchParams превратят их в литеральную строку "null" */
    cleanRange(range)
    {
        return Object.fromEntries(Object.entries(range).filter(([, value]) => value !== null && value !== undefined));
    }

    /**
     * @param {{type: string, address: string, body: string}} payload
     * @return {Promise<axios.AxiosResponse<{id: number}>>}
     */
    createDaily(payload)
    {
        return axios.post(this.routers.queue(), payload);
    }

    /**
     * @param {number} id
     * @param {{address: string, body: string}} payload
     * @return {Promise<axios.AxiosResponse<{id: number}>>}
     */
    updateDaily(id, payload)
    {
        return axios.put(this.routers.queueItem(id), payload);
    }

    /**
     * @param {number} id
     * @return {Promise<axios.AxiosResponse<void>>}
     */
    deleteDaily(id)
    {
        return axios.delete(this.routers.queueItem(id));
    }
}

const messagingAPI = new MessagingAPI;

export {
    messagingAPI,
}
