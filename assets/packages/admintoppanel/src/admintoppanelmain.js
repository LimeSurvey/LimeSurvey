import Vue from 'vue';
import VModal from 'vue-js-modal';
import TopBarPanel from './components/TopBarPanel.vue';
import getAppState from "./storage/store.js";
import {PluginLog, LOG} from "./mixins/logSystem.js";
import Loader from './helperComponents/loader.vue';

//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];
Vue.config.devtools = true;

Vue.use( PluginLog );
Vue.use( VModal, {
  dynamic: true,
});

Vue.component('loader-widget', Loader);

let surveyid = 'newSurvey';
if(window.LS != undefined) {
    surveyid = window.LS.parameters.$GET.surveyid || window.LS.parameters.keyValuePairs.surveyid;
}

const AppState = getAppState(LS.globalUserId, surveyid);
   
if(window.TopBarVue === undefined)  {
    const TopBarVue = new Vue({
        el: "#vue-topbar-container",
        store: AppState,
        components: {
            topbar: TopBarPanel,
        },
    });
    window.TopBarVue = true;
}

