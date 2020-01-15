import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import VueLocalStorage from 'vue-localstorage';

import mutations from './mutations';
import actions from './actions';
import getters from './getters';
import statePreset from './state';

Vue.use(VueLocalStorage);
Vue.use(Vuex);


export default function(userid = null){
    const vuexLocal = new VuexPersistence({
        key: userid == null ? 'lsglobalsidemenu_'+userid : 'lsglobalsidemenu',
        storage: window.localStorage
    });
    
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
