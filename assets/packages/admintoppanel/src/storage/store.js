import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import VueLocalStorage from 'vue-localstorage';

import mutations from './mutations';
import actions from './actions';
import statePreset from './state';

Vue.use(VueLocalStorage);
Vue.use(Vuex);


export default function(user_id, surveyId){
    const vuexLocal = new VuexPersistence({
        key: 'lstopbar_'+user_id+'_'+surveyId,
        storage: window.localStorage
    });
    
    return new Vuex.Store({
        state: statePreset,
        plugins: [
            vuexLocal.plugin
        ],
        mutations,
        actions,
        getters: {
            surveyid: (state) => (LS.parameters.$GET.surveyid || LS.parameters.keyValuePairs.surveyid || null)
        }
    });
}
