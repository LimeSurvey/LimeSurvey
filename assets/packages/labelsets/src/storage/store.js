import Vue from 'vue'
import Vuex from 'vuex'
import VuexPersistence from 'vuex-persist';
import VueLocalStorage from 'vue-localstorage';

import state from './state.js';
import mutations from './mutations.js';
import actions from './actions.js';

Vue.use(VueLocalStorage);
Vue.use(Vuex)

export default function(userId){
  const vuexLocal = new VuexPersistence({
    key: 'lslabelsetedit_'+userId,
    storage: window.localStorage
  });

  return new Vuex.Store({
    plugins: [
        vuexLocal.plugin
    ],
    state,
    mutations,
    actions,
  });
}
