import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import VueLocalStorage from 'vue-localstorage';

import mutations from './mutations';
import actions from './actions';

Vue.use(VueLocalStorage);
Vue.use(Vuex);


export default function(questionId){
    const vuexLocal = new VuexPersistence({
        key: 'lsquestionedit_'+questionId,
        storage: window.localStorage
    });

    const statePreset = {
        currentQuestion: {},
        questionAttributes: {},
        questionImmutable: {},
        survey: {},
    };

    new Vuex.Store({
        state: statePreset,
        plugins: [
            vuexLocal.plugin
        ],
        mutations,
        actions,
    });
}
