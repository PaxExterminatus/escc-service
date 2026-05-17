class ProfileSex {
    constructor() {
        this.options = [
            {name: 'Мужской', code: 'man'},
            {name: 'Женский', code: 'woman'},
            {name: 'Неизвестный', code: 'unknown'},
        ];
    }

    get man() {
        return this.options[0]
    }

    get woman() {
        return this.options[1]
    }

    get unknown() {
        return this.options[2]
    }
}

const profileSex = new ProfileSex()
const profileSexOptions = profileSex.options

export {
    ProfileSex,
    profileSex,
    profileSexOptions,
}
