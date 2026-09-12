import {profileAPI} from './ProfileAPI';
import {profileSex} from './ProfileSex';

class Profile {

    constructor() {
        this.api = profileAPI;
        this.id = null
        this.name = null
        this.name_last = null
        this.name_middle = null
        this.birthday = null
        this.sex = null;
        this.phone = null;
        this.sms_allowed = false;
        this.email = null;
        this.email_allowed = false;
    }

    /**
     *
     * @param {string|number|null} id
     * @return {Profile}
     */
    static empty({id = null})
    {
        const profile = new Profile;
        profile.id = id;
        return profile;
    }

    /**
     * @param {ProfileData} data
     */
    fill(data)
    {
        this.id = data.id
        this.name = data.name
        this.name_last = data.name_last
        this.name_middle = data.name_middle
        this.birthday = data.birthday
        this.sex = profileSex[data.sex]
        this.phone = data.phone
        this.sms_allowed = data.sms_allowed
        this.email = data.email
        this.email_allowed = data.email_allowed

        return this;
    }
}

export {
    Profile,
}
