import {financeHistoryAPI} from './FinanceHistoryAPI';

class FinanceHistory {

    constructor() {
        this.api = financeHistoryAPI;
        this.items = [];
    }

    /**
     * @param {FinanceHistoryItemData[]} data
     */
    fill(data)
    {
        this.items = data;

        return this;
    }
}

export {
    FinanceHistory,
}
