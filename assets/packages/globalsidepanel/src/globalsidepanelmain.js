import Vue from 'vue'
import GlobalSidemenu from './components/GlobalSidemenu.vue'
import getStore from './storage/store'
import {PluginLog} from "./mixins/logSystem.js";

Vue.config.productionTip = false

Vue.use(PluginLog);
Vue.mixin({
  methods: {
      updatePjaxLinks: function () {
          //this.$store.dispatch("updatePjax");
          this.$forceUpdate();
      },
      redoTooltips: function () {
          window.LS.doToolTip();
      },
      translate(string){
          return window.GlobalSideMenuData.i10n[string] || string;
      }
  },
  filters: {
      translate(string){
          return window.GlobalSideMenuData.i10n[string] || string;
      }
  }
});

let storeName = window.GlobalSideMenuData.sgid ? LS.globalUserId+'-'+window.GlobalSideMenuData.sgid : LS.globalUserId;

const AppState = getStore(storeName);

const GlobalSidePanel = new Vue({
  el: "#global-sidebar-container",
  store: AppState,
  components: {
    GlobalSidemenu
  },
  mounted() {
      $(document).on("vue-redraw", () => {
          this.$forceUpdate();
      });
      $(document).trigger("vue-reload-remote");
  }
});