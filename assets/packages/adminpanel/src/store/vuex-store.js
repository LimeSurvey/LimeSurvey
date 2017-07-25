import Vue from 'vue';
import Vuex from 'vuex';
import VueLocalStorage from 'vue-localstorage';
import createPersist from 'vuex-localstorage';
import Pjax from 'pjax';


Vue.use(Vuex);
Vue.use(VueLocalStorage);


const getAppState = function(userid){
  const statePreset = {
            surveyid: 0,
            language: '',
            maxHeight: 0,
            currentUser: userid,
            currentTab: 'settings',
            sidebarwidth: '380px',
            isCollapsed: false,
            pjax: null,
            pjaxLoading: false,
            lastMenuOpen: null,
            lastMenuItemOpen: null,
            lastQuestionOpen: null,
            lastQuestionGroupOpen: null,
            questionGroupOpenArray: [],
            collapsedmenus: null,
            sidemenus: null,
            topmenus: null,
            bottommenus: null,
          };

  return new Vuex.Store({
      plugins: [
        createPersist({
          initialState: statePreset,
          namespace: userid+'_adminpanel_settings',
          expires: 365 * 24 * 60 * 60 * 1e3 //one year
        })
      ],
      state: statePreset,
      getters:{
        substractContainer: state => {
          let bodyWidth =  ($('#vue-app-main-container').width()-parseInt(state.sidebarwidth));
          let collapsedBodyWidth = ($('#vue-app-main-container').width()-parseInt('98px'));
          return (state.isCollapsed ? collapsedBodyWidth : bodyWidth)+'px';
        }
      },
      mutations: {
        updateSurveyId (state, newSurveyId) {
          state.surveyid = newSurveyId
        },
        changeLanguage (state, language) {
          state.language = language;
        },
        changeCurrentTab (state, value){
          state.currentTab = value;
        },
        changeSidebarwidth (state, value){
          state.sidebarwidth = value;
        },
        changeIsCollapsed (state, value){
          state.isCollapsed = value;
        },
        changeMaxHeight(state, newHeight){
          state.maxHeight = newHeight;
        },     
        changeCurrentUser(state, newUser){
          state.currentUser = newUser;
        },
        closeAllMenus(state){
            state.lastMenuOpen = null;
            state.lastMenuItemOpen = null;
            state.lastQuestionGroupOpen = null;
            state.lastQuestionOpen = null;
        },
        lastMenuItemOpen(state, menuItem){
            state.lastMenuOpen = menuItem.menu_id;
            state.lastMenuItemOpen = menuItem.id;
            state.lastQuestionGroupOpen = null;
            state.lastQuestionOpen = null;
        },
        lastMenuOpend(state,menuObject){
            state.lastMenuOpen = menuObject.id;
            state.lastQuestionOpen = null;
            state.lastMenuItemOpen = null;
        },
        lastQuestionOpen(state, questionObject){
            state.lastQuestionGroupOpen = questionObject.gid
            state.lastQuestionOpen = questionObject.qid;
            state.lastMenuItemOpen = null;
        },
        lastQuestionGroupOpen(state, questionGroupObject){
            state.lastQuestionGroupOpen = questionGroupObject.gid;
            state.lastQuestionOpen = null;
        },
        questionGroupOpenArray(state, questionGroupOpenArray){
            state.questionGroupOpenArray = questionGroupOpenArray;
        },
        updateQuestiongroups(state, questiongroups){
            state.questiongroups = questiongroups;
        },
        updateCollapsedmenus(state, collapsedmenus){
            state.collapsedmenus = collapsedmenus;
        },
        updateSidemenus(state, sidemenus){
            state.sidemenus = sidemenus;
        },
        updateTopmenus(state, topmenus){
            state.topmenus = topmenus;
        },
        updateBottommenus(state, bottommenus){
            state.bottommenus = bottommenus;
        },
        updatePjax(state){
          console.log('PJAX updated');
          state.pjax = null;
          state.pjax = new Pjax({
            elements: "a.pjax", // default is "a[href], form[action]"
            selectors: [
              '#pjax-content',
              '#breadcrumb-container'
              ]
          });
        }    
      }
  });
};

export default getAppState;
