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
    language: '',
    maxHeight: 0,
    currentUser: 0,
  },
  mutations: {
    updateSurveyId (state, newSurveyId) {
      state.surveyid = newSurveyId
    },
    changeLanguage (state, language) {
      state.language = language;
    },
    changeMaxHeight(state, newHeight){
      state.maxHeight = newHeight;
    },    
    changeCurrentUser(state, newUser){
      state.currentUser = newUser;
    }    
  }
});

if(document.getElementById('vue-app-main-container')){
  const sidemenu = new Vue(
  {  
    el: '#vue-app-main-container',
    store: AppState,
    components: {
      'sidebar' : Sidebar,
    },
    methods: {
    },
    
    mounted(){
       this.$store.commit('updateSurveyId', $(this.$el).data('surveyid'));
       this.$store.commit('changeMaxHeight', ($('#in_survey_common').height()-35));
       this.$store.commit('changeCurrentUser', $('#currentUserId').val());
    }
  });
}

const pjaxed = new Pjax({
  elements: "a.pjax", // default is "a[href], form[action]"
  selectors: [
    "#pjax-content",
    '#breadcrumb-container'
    ]
});

$(document).ready(()=>{
  if($('#vue-app-main-container').length >0 ){

    const
        menuOffset = $('nav.navbar').outerHeight()+45,
        menuHeight = + $('.menubar.surveymanagerbar').outerHeight(),
        footerHeight =  $('footer').outerHeight()+65,
        documentHeight = screen.availableHeight || screen.height,
        innerMenuHeight = $('#surveybarid').outerHeight();
    
    let vueAppContainerHeight = documentHeight-( menuOffset + menuHeight + footerHeight );
    let inSurveyCommonHeight = vueAppContainerHeight - (innerMenuHeight + 45);

    console.log({
      menuOffset : menuOffset,
      menuHeight : menuHeight,
      footerHeight : footerHeight,
      documentHeight : documentHeight,
      innerMenuHeight : innerMenuHeight,
      vueAppContainerHeight : vueAppContainerHeight,
      inSurveyCommonHeight : inSurveyCommonHeight
    });

    $('#vue-app-main-container').css('height', vueAppContainerHeight+'px');
    $('#in_survey_common').css('height',inSurveyCommonHeight+'px');
  }
});

// const topmenu = new Vue(
//   {  
//     el: '#vue-top-menu-app',
//     components: {
//       'topbar' : Topbar,
//     } 
// });
