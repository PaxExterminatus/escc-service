import { createStore } from 'vuex';
import auth from 'app/store/authModule';
import audio from 'cmp/media/AudioPlayer/AudioPlayerStore';
import video from 'cmp/media/VideoPlayer/VideoPlayerStore';
import reader from 'cmp/media/LessonReader/LessonReaderStore';

const store = createStore({
    modules: {
        auth,
        audio,
        video,
        reader,
    },
    state: {},
    mutations: {},
});

export default store;
