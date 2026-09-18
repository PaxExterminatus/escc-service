import axios from 'axios';

class InvoiceAPI {

    routers = {
        containerInvoice: (id) => `/api/invoice/container/${id}`,
        containerInvoiceEmailPreview: (id) => `/api/invoice/container/${id}/email-preview`,
        containerInvoiceEmail: (id) => `/api/invoice/container/${id}/email`,
        range: (from, to) => `/api/invoice/range/${from}/${to}`,
        rangePrint: (from, to) => `/api/invoice/range/${from}/${to}/print`,
    };

    /**
     * @param {string|int} id
     * @return {string}
     */
    containerInvoiceUrl(id)
    {
        return this.routers.containerInvoice(id);
    }

    /**
     * Предпросмотр письма со счётом для контейнера (без отправки)
     *
     * @param {string|int} id
     * @return {Promise<axios.AxiosResponse<{html: string}>>}
     */
    containerInvoiceEmailPreview(id)
    {
        return axios.get(this.routers.containerInvoiceEmailPreview(id));
    }

    /**
     * Отправить счёт по email вместо печати
     *
     * @param {string|int} id
     * @return {Promise<axios.AxiosResponse<{response: {status: number, reason: string}}>>}
     */
    sendContainerInvoiceEmail(id)
    {
        return axios.post(this.routers.containerInvoiceEmail(id));
    }

    /**
     * @param {string} from формат YYYY-MM-DD
     * @param {string} to формат YYYY-MM-DD
     * @return {Promise<axios.AxiosResponse<{containers: InvoiceContainerRow[]}>>}
     */
    range(from, to)
    {
        return axios.get(this.routers.range(from, to));
    }

    /**
     * @param {string} from формат YYYY-MM-DD
     * @param {string} to формат YYYY-MM-DD
     * @return {string}
     */
    rangePrintUrl(from, to)
    {
        return this.routers.rangePrint(from, to);
    }
}

const invoiceAPI = new InvoiceAPI;

export {
    invoiceAPI,
}
