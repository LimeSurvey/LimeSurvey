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
        currentQuestion: {},
        currentQuestionSubquestions: {},
        currentQuestionAnswerOptions: {},
        currentQuestionSettings: {},
        currentQuestionI10N: {},
        currentQuestionAttributes: {},
        currentQuestionGeneralSettings: [],
        currentQuestionAdvancedSettings: {},
        questionAttributesImmutable: {},
        questionGeneralSettingsImmutable: [],
        questionAdvancedSettingsImmutable: {},
        questionImmutable: {},
        questionImmutableI10N: {},
        questionSubquestionsImmutable: {},
        questionAnswerOptionsImmutable: {},
        languages: [],
        survey: {},
        questionTypes: [
            {
                code : '',
                type : '',
                title : ''
            }
        ],
        questionAdvancedSettingsCategory: '',
        activeLanguage: '',
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
