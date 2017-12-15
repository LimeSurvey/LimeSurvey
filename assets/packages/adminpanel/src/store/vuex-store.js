import Vue from 'vue';
import Vuex from 'vuex';
import VueLocalStorage from 'vue-localstorage';
import createPersist from 'vuex-localstorage';

Vue.use(Vuex);
Vue.use(VueLocalStorage);

const getAppState = function (userid) {
    const statePreset = {
        surveyid: 0,
        language: '',
        maxHeight: 0,
        inSurveyViewHeight: 400,
        generalContainerHeight: 380,
        sideBarHeight: 400,
        currentUser: userid,
        currentTab: 'settings',
        sidebarwidth: '380px',
        maximalSidebar: false,
        isCollapsed: false,
        pjax: null,
        pjaxLoading: false,
        lastMenuOpen: false,
        lastMenuItemOpen: false,
        lastQuestionOpen: false,
        lastQuestionGroupOpen: false,
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
                namespace: userid + '_adminpanel_settings',
                expires: 365 * 24 * 60 * 60 * 1e3 //one year
            })
        ],
        state: statePreset,
        getters: {
            substractContainer: state => {
                let bodyWidth = ($('#vue-app-main-container').width() - parseInt(state.sidebarwidth));
                let collapsedBodyWidth = ($('#vue-app-main-container').width() - parseInt('98px'));
                return (state.isCollapsed ? collapsedBodyWidth : bodyWidth) + 'px';
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
            changeGeneralContainerHeight(state, newHeight) {
                state.generalContainerHeight = newHeight;
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
                let event = new Event('pjax:refresh');
                window.dispatchEvent(event);                
            }
        }
    });
};

export default getAppState;
