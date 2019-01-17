import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import VueLocalStorage from 'vue-localstorage';

import mutations from './mutations';
import actions from './actions';
import getters from './getters';

Vue.use(VueLocalStorage);
Vue.use(Vuex);


export default function(questionId){
    const vuexLocal = new VuexPersistence({
        key: 'lsquestionedit_'+questionId,
        storage: window.localStorage
    });

    const statePreset = {
        currentQuestionQuestion: '',
        currentQuestionHelp: '',
        currentQuestion: {},
        currentQuestionSettings: {},
        currentQuestionI10N: {},
        questionAttributes: {},
        questionImmutable: {},
        questionImmutableI10N: {},
        languages: [],
        activeLanguage: '',
        survey: {},
        questionTypes: [
            {
                code : '',
                type : '',
                title : ''
            }
        ],
    };

    return new Vuex.Store({
        state: statePreset,
        plugins: [
            vuexLocal.plugin
        ],
        mutations,
        actions,
        getters
    });
}
