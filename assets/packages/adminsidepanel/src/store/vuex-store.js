import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import VueLocalStorage from 'vue-localstorage';

import statePreset from './state';
import getters from './getters';
import mutations from './mutations';
import actions from './actions';

Vue.use(VueLocalStorage);
Vue.use(Vuex);


const getAppState = function (userid,surveyid) {
    const AppStateName = 'limesurveyadminsidepanel';

    
    for (let i = sessionStorage.length - 1; i >= 0; i--) {
      const key = sessionStorage.key(i);

      if (key && key.includes(AppStateName) && key !== AppStateName+'_'+userid+'_'+surveyid) {
          sessionStorage.removeItem(key);
      }
    }
  
    const vuexLocal = new VuexPersistence({
        key: AppStateName+'_'+userid+'_'+surveyid,
        storage: window.sessionStorage
    });


    return new Vuex.Store({
        state: statePreset(userid),
        plugins: [
            vuexLocal.plugin
        ],
        getters,
        mutations,
        actions
    });
};

export default getAppState;
