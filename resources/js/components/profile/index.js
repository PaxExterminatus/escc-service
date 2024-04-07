import ProfileCard from './ProfileCardComponent.vue'
import {profileAPI} from './ProfileAPI.js';
import {Profile} from './Profile.js';

const profile = {
    api: profileAPI,
};

export {
    Profile,
    profileAPI,
    ProfileCard,
}
