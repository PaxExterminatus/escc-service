import axios from 'axios';

class InvoiceAPI {

    routers = {
        containerInvoice: (id) => `/api/invoice/container/${id}`,
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
