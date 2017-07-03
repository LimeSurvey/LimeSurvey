import Vue from 'vue';
import Vuex from 'vuex';
import VueLocalStorage from 'vue-localstorage';
import _ from 'lodash';
import Sidebar from './components/sidebar.vue';
import Topbar from './components/topbar.vue';
import Pjax from 'pjax';

Vue.use(Vuex);
Vue.use(VueLocalStorage);

const AppState = new Vuex.Store({
  state: {
    surveyid: 0,
    language: ''
  },
  mutations: {
    updateSurveyId (state, newSurveyId) {
      state.surveyid = newSurveyId
    },
    changeLanguage (state, language) {
      state.language = language;
    }    
  }
});

if(document.getElementById('vue-side-menu-app')){
  const sidemenu = new Vue(
  {  
    el: '#vue-side-menu-app',
    store: AppState,
    components: {
      'sidebar' : Sidebar,
    },
    mounted(){
       this.$store.commit('updateSurveyId', $(this.$el).data('surveyid'));
    } 
});
}

const pjaxed = new Pjax({
          elements: "a.pjax", // default is "a[href], form[action]"
          selectors: [
            "#in_survey_common"
            ]
        });


// const topmenu = new Vue(
//   {  
//     el: '#vue-top-menu-app',
//     components: {
//       'topbar' : Topbar,
//     } 
// });
