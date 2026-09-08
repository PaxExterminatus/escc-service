import axios from 'axios';

class FinanceHistoryAPI {

    routers = {
        // тот же прокси-эндпоинт, которым пользуется escc-cabinet (App\Domain\Cabinet)
        get: (id) => `/api/client/${id}/finance-history`,
    };

    /**
     * @param {string|int} id
     * @return {Promise<axios.AxiosResponse<{data: FinanceHistoryItemData[]}>>}
     */
    get(id)
    {
        return axios.get(this.routers.get(id));
    }
}

const financeHistoryAPI = new FinanceHistoryAPI;

export {
    financeHistoryAPI,
}
