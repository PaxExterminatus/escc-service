import axios from 'axios';

class MessagingAPI {

    routers = {
        recipient: (clientId) => `/api/messages/recipient/${clientId}`,
        send: () => `/api/messages/send`,
        daily: (type) => `/api/messages/daily/${type}`,
        dailySend: (type) => `/api/messages/daily/${type}/send`,
        dailyTxt: (type) => `/api/messages/daily/${type}/txt`,
    };

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
     * @return {Promise<axios.AxiosResponse<{messages: object[]}>>}
     */
    daily(type)
    {
        return axios.get(this.routers.daily(type));
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
     * @return {string}
     */
    dailyTxtUrl(type)
    {
        return this.routers.dailyTxt(type);
    }
}

const messagingAPI = new MessagingAPI;

export {
    messagingAPI,
}
