import Vue from 'vue'
import App from './App.vue'
import VModal from 'vue-js-modal'
import VuejsDialog from "vuejs-dialog"

import {PluginLog} from "./mixins/logSystem";
import store from './storage/store'

import 'vue2-dropzone/dist/vue2Dropzone.min.css'


Vue.config.productionTip = false

Vue.use(VuejsDialog)
Vue.use(VModal, { dynamic: true })

Vue.use( PluginLog );

Vue.mixin({
  methods: {
      translate(value) {
          return window.FileManager.i10N[value] || value;
      }
  },
  filters: {
      translate: (value) => {
          return window.FileManager.i10N[value] || value;
      }
  }
});
const fileManager = new Vue({
  el: '#limeSurveyFileManager',
  store: store(),
  components: {filemanager: App},
});