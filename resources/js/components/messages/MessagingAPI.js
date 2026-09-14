import axios from 'axios';

class MessagingAPI {

    routers = {
        templates: () => `/api/messages/templates`,
        template: (id) => `/api/messages/templates/${id}`,
        templateRender: (id) => `/api/messages/templates/${id}/render`,
        recipient: (clientId) => `/api/messages/recipient/${clientId}`,
        send: () => `/api/messages/send`,
        daily: (type) => `/api/messages/daily/${type}`,
        dailySend: (type) => `/api/messages/daily/${type}/send`,
        dailyTxt: (type) => `/api/messages/daily/${type}/txt`,
    };

    /**
     * @param {boolean} activeOnly
     * @return {Promise<axios.AxiosResponse<{data: MessageTemplateData[]}>>}
     */
    templates(activeOnly = false)
    {
        return axios.get(this.routers.templates(), {params: {active_only: activeOnly}});
    }

    /**
     * @param {{code: string, name: string, body: string, is_active: boolean}} payload
     * @return {Promise<axios.AxiosResponse<{data: MessageTemplateData}>>}
     */
    createTemplate(payload)
    {
        return axios.post(this.routers.templates(), payload);
    }

    /**
     * @param {number} id
     * @param {{code: string, name: string, body: string, is_active: boolean}} payload
     * @return {Promise<axios.AxiosResponse<{data: MessageTemplateData}>>}
     */
    updateTemplate(id, payload)
    {
        return axios.put(this.routers.template(id), payload);
    }

    /**
     * @param {number} id
     * @return {Promise<axios.AxiosResponse<void>>}
     */
    deleteTemplate(id)
    {
        return axios.delete(this.routers.template(id));
    }

    /**
     * @param {number} templateId
     * @param {string|number} clientId
     * @return {Promise<axios.AxiosResponse<{body: string}>>}
     */
    renderTemplate(templateId, clientId)
    {
        return axios.get(this.routers.templateRender(templateId), {params: {client_id: clientId}});
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
