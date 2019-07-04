import Vue from 'vue'
import TopBarPanel from './components/TopBarPanel.vue';
import getAppState from "./store/vuex-store.js";
import {PluginLog} from "./mixins/logSystem.js";
import Loader from './helperComponents/loader.vue';

//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];

Vue.use( PluginLog );
Vue.use( LSCKEditor );

Vue.component('loader-widget', Loader);

Vue.mixin({
  methods: {
      translate: (value) => {
          return window.TopBarData.i10N[value] || value;
      }
  },
  filters: {
      translate: (value) => {
          return window.TopBarData.i10N[value] || value;
      }
  }
});

let surveyid = 'newSurvey'; 
if(window.LS != undefined) {
    surveyid = window.LS.parameters.$GET.surveyid || window.LS.parameters.keyValuePairs.surveyid;
}

const AppState = getAppState(LS.globalUserId, surveyid);

const topBarVue =  new Vue({
  el: "#vue-topbar-container",
  store: AppState,
  components: {
      topbar: TopBarPanel,
  },
});