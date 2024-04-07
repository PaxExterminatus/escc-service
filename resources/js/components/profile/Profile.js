import {profileAPI} from './ProfileAPI';

class Profile {

    constructor() {
        this.api = profileAPI;
        this.id = null
        this.name = null
        this.name_last = null
        this.name_middle = null
        this.birthday = null
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

        return this;
    }
}

export {
    Profile,
}
