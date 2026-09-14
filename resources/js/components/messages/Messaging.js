import {messagingAPI} from './MessagingAPI';

class Messaging {

    constructor() {
        this.api = messagingAPI;
        this.templates = [];
        /** @type {{code: string, label: string, address: (string|null), allowed: boolean}[]} */
        this.channels = [];
    }

    /**
     * @param {MessageTemplateData[]} templates
     * @param {{channels: object[]}} recipient
     */
    fill(templates, recipient)
    {
        this.templates = templates;
        this.channels = recipient.channels;

        return this;
    }

    /**
     * @param {string} code
     * @return {object|undefined}
     */
    channel(code)
    {
        return this.channels.find((c) => c.code === code);
    }

    /**
     * @param {string} code
     * @return {boolean}
     */
    isChannelAllowed(code)
    {
        return this.channel(code)?.allowed ?? false;
    }
}

export {
    Messaging,
}
