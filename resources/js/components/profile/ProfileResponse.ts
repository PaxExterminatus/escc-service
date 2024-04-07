interface ProfileResponseData {
    profile: ProfileData
}

interface ProfileData {
    id: number
    name: string|null
    name_last: string|null
    name_middle: string|null
    birthday: string|null
}