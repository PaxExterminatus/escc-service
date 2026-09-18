import axios from 'axios';

class TemplateAPI {

    routers = {
        templates: () => `/api/templates`,
        template: (id) => `/api/templates/${id}`,
        templateRender: (id) => `/api/templates/${id}/render`,
        templatePreview: (id) => `/api/templates/${id}/preview`,
        wrappers: () => `/api/templates/wrappers`,
        operations: () => `/api/templates/operations`,
        operationFile: (operationId) => `/api/templates/operations/${operationId}/file`,
        emailPreview: () => `/api/templates/email-preview`,
    };

    /**
     * Самостоятельные шаблоны сообщений (SMS/Email)
     *
     * @param {boolean} activeOnly
     * @return {Promise<axios.AxiosResponse<{data: TemplateData[]}>>}
     */
    index(activeOnly = false)
    {
        return axios.get(this.routers.templates(), {params: {active_only: activeOnly}});
    }

    /**
     * Библиотека обёрток письма (шапка+футер) — из них content-шаблоны выбирают себе одну
     *
     * @return {Promise<axios.AxiosResponse<{data: TemplateData[]}>>}
     */
    wrappers()
    {
        return axios.get(this.routers.wrappers());
    }

    /**
     * @param {{code: string, name: string, body: string, is_active: boolean, type_id?: number, wrapper_id?: number|null, is_default?: boolean}} payload
     * @return {Promise<axios.AxiosResponse<{data: TemplateData}>>}
     */
    create(payload)
    {
        return axios.post(this.routers.templates(), payload);
    }

    /**
     * @param {number} id
     * @param {{code: string, name: string, body: string, is_active: boolean, wrapper_id?: number|null, is_default?: boolean}} payload
     * @return {Promise<axios.AxiosResponse<{data: TemplateData}>>}
     */
    update(id, payload)
    {
        return axios.put(this.routers.template(id), payload);
    }

    /**
     * @param {number} id
     * @return {Promise<axios.AxiosResponse<void>>}
     */
    delete(id)
    {
        return axios.delete(this.routers.template(id));
    }

    /**
     * @param {number} templateId
     * @param {string|number} clientId
     * @return {Promise<axios.AxiosResponse<{body: string}>>}
     */
    render(templateId, clientId)
    {
        return axios.get(this.routers.templateRender(templateId), {params: {client_id: clientId}});
    }

    /**
     * Операции и назначенные им шаблоны
     *
     * @return {Promise<axios.AxiosResponse<{operations: TemplateOperationRow[]}>>}
     */
    operations()
    {
        return axios.get(this.routers.operations());
    }

    /**
     * @param {number} operationId
     * @param {number} typeId
     * @param {string} name
     * @param {{file: File}|{body: string}} content
     * @param {number|null} wrapperId только для html — какую обёртку письма использовать (игнорируется, если wrapperAuto)
     * @param {boolean} wrapperAuto только для html — выбрать обёртку по балансу клиента
     * @return {Promise<axios.AxiosResponse>}
     */
    assignOperation(operationId, typeId, name, content, wrapperId = null, wrapperAuto = false)
    {
        const formData = new FormData();
        formData.append('operation_id', operationId);
        formData.append('type_id', typeId);
        formData.append('name', name);

        if (content.file) {
            formData.append('file', content.file);
        } else {
            formData.append('body', content.body);
            formData.append('wrapper_auto', wrapperAuto ? '1' : '0');

            if (wrapperId && !wrapperAuto) {
                formData.append('wrapper_id', wrapperId);
            }
        }

        return axios.post(this.routers.operations(), formData, {
            headers: {'Content-Type': 'multipart/form-data'},
        });
    }

    /**
     * @param {number} operationId
     * @return {string}
     */
    operationFileUrl(operationId)
    {
        return this.routers.operationFile(operationId);
    }

    /**
     * Предпросмотр любого шаблона — как его увидит получатель. Сборкой занимается сервер
     * (EmailComposer), поэтому предпросмотр не может разойтись с реальной отправкой.
     *
     * @param {number} templateId
     * @return {Promise<axios.AxiosResponse<{html: string}>>}
     */
    preview(templateId)
    {
        return axios.get(this.routers.templatePreview(templateId));
    }

    /**
     * Предпросмотр email-сообщения, обёрнутого в обёртку письма (своя у шаблона — если он
     * передан — либо обёртка по умолчанию)
     *
     * @param {string|number} clientId
     * @param {string} body
     * @param {number|null} templateId
     * @return {Promise<axios.AxiosResponse<{html: string}>>}
     */
    emailPreview(clientId, body, templateId = null)
    {
        return axios.post(this.routers.emailPreview(), {client_id: clientId, body, template_id: templateId});
    }
}

const templateAPI = new TemplateAPI;

export {
    templateAPI,
}
