import Vue from 'vue'

import ParameterTable from "./components/parameter-table.vue";
import getAppState from "./store/vuex-store.js";
import {PluginLog} from "./mixins/logSystem.js";
import Loader from './helperComponents/loader.vue';

Vue.config.productionTip = false

Vue.use(PluginLog);

Vue.mixin({
  filters: {
    translate(string) {
      return window.PanelIntegrationData.i10n[string] || string;
    }
  },
  methods: {
    translate(string) {
      return window.PanelIntegrationData.i10n[string] || string;
    }
  }
})

const surveyid = window.PanelIntegrationData.surveyid;
const userID = LS.globalUserId;

const AppState = getAppState(surveyid, userID)

const parameterTable = new Vue({
    el: "#vue-parameter-table-container",
    store: AppState,
    components: {
        lspanelparametertable: ParameterTable,
    },
});