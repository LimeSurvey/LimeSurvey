import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import VueLocalStorage from 'vue-localstorage';

Vue.use(VueLocalStorage);
Vue.use(Vuex);


const getAppState = function (userid) {

    const vuexLocal = new VuexPersistence({
        key: 'limesurveyadminpanel_'+userid,
        storage: window.localStorage
    });

    const statePreset = {
        surveyid: 0,
        language: '',
        maxHeight: 0,
        inSurveyViewHeight: 400,
        sideBodyHeight: '100%',
        sideBarHeight: 400,
        currentUser: userid,
        currentTab: 'settings',
        sidebarwidth: 380,
        maximalSidebar: false,
        isCollapsed: false,
        pjax: null,
        pjaxLoading: false,
        lastMenuOpen: false,
        lastMenuItemOpen: false,
        lastQuestionOpen: false,
        lastQuestionGroupOpen: false,
        questionGroupOpenArray: [],
        questiongroups: [],
        collapsedmenus: null,
        sidemenus: null,
        topmenus: null,
        bottommenus: null,
        surveyActiveState: false
    };

    return new Vuex.Store({
        state: statePreset,
        plugins: [
            vuexLocal.plugin
        ],
        getters: {
            substractContainer: state => {
                let bodyWidth = (1 - (parseInt(state.sidebarwidth)/$('#vue-app-main-container').width()))*100;
                let collapsedBodyWidth = (1 - (parseInt('98px')/$('#vue-app-main-container').width()))*100;
                return Math.floor(state.isCollapsed ? collapsedBodyWidth : bodyWidth) + '%';
            },
            sideBarSize : state => {
                let sidebarWidth = (parseInt(state.sidebarwidth)/$('#vue-app-main-container').width())*100;
                let collapsedSidebarWidth = (parseInt(98)/$('#vue-app-main-container').width())*100;
                return Math.ceil(state.isCollapsed ? collapsedSidebarWidth : sidebarWidth) + '%';
            }
        },
        mutations: {
            updateSurveyId(state, newSurveyId) {
                state.surveyid = newSurveyId;
            },
            changeLanguage(state, language) {
                state.language = language;
            },
            changeCurrentTab(state, value) {
                state.currentTab = value;
            },
            changeSidebarwidth(state, value) {
                state.sidebarwidth = value;
            },
            maxSideBarWidth(state, value) {
                state.maximalSidebar = value;
            },
            changeIsCollapsed(state, value) {
                state.isCollapsed = value;
                $(document).trigger('vue-sidemenu-update-link');
            },
            changeMaxHeight(state, newHeight) {
                state.maxHeight = newHeight;
            },
            changeSideBarHeight(state, newHeight) {
                state.sideBarHeight = newHeight;
            },
            changeInSurveyViewHeight(state, newHeight) {
                state.inSurveyViewHeight = newHeight;
            },
            changeSideBodyHeight(state, newHeight) {
                state.sideBodyHeight = newHeight+'px' || '100%';
            },
            changeCurrentUser(state, newUser) {
                state.currentUser = newUser;
            },
            closeAllMenus(state) {
                state.lastMenuOpen = false;
                state.lastMenuItemOpen = false;
                state.lastQuestionGroupOpen = false;
                state.lastQuestionOpen = false;
            },
            lastMenuItemOpen(state, menuItem) {
                state.lastMenuOpen = menuItem.menu_id;
                state.lastMenuItemOpen = menuItem.id;
                state.lastQuestionGroupOpen = false;
                state.lastQuestionOpen = false;
            },
            lastMenuOpen(state, menuObject) {
                state.lastMenuOpen = menuObject.id;
                state.lastQuestionOpen = false;
                state.lastMenuItemOpen = false;
            },
            lastQuestionOpen(state, questionObject) {
                state.lastQuestionGroupOpen = questionObject.gid;
                state.lastQuestionOpen = questionObject.qid;
                state.lastMenuItemOpen = false;
            },
            lastQuestionGroupOpen(state, questionGroupObject) {
                state.lastQuestionGroupOpen = questionGroupObject.gid;
                state.lastQuestionOpen = false;
            },
            questionGroupOpenArray(state, questionGroupOpenArray) {
                state.questionGroupOpenArray = questionGroupOpenArray;
            },
            updateQuestiongroups(state, questiongroups) {
                state.questiongroups = questiongroups;
            },
            addToQuestionGroupOpenArray(state, questiongroupToAdd) {
                state.questionGroupOpenArray.push(questiongroupToAdd.gid);
            },
            updateCollapsedmenus(state, collapsedmenus) {
                state.collapsedmenus = collapsedmenus;
            },
            updateSidemenus(state, sidemenus) {
                state.sidemenus = sidemenus;
            },
            updateTopmenus(state, topmenus) {
                state.topmenus = topmenus;
            },
            updateBottommenus(state, bottommenus) {
                state.bottommenus = bottommenus;
            },
            updatePjax(state) {
                $(document).trigger('pjax:refresh');           
            },
            setSurveyActiveState(state, surveyState) {
                state.surveyActiveState = !!surveyState;
            }
        }
    });
};

export default getAppState;
