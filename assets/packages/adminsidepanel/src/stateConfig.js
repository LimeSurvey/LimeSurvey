/**
 * State configuration for adminsidepanel
 * Defines default state, mutations, and getters
 */

/**
 * Create default state
 * @param {string|number} userid
 * @param {string|number} surveyid
 * @returns {Object}
 */
export function createDefaultState(userid, surveyid) {
    // Calculate default sidebar width
    let sidebarWidth = $('#vue-apps-main-container').width() * 0.33;
    if ($('#vue-apps-main-container').width() > 1400) {
        sidebarWidth = $('#vue-apps-main-container').width() * 0.25;
    }

    return {
        surveyid: 0,
        language: '',
        maxHeight: 0,
        inSurveyViewHeight: 1000,
        sideBodyHeight: '100%',
        sideBarHeight: 400,
        currentUser: userid,
        currentTab: 'settings',
        sidebarwidth: sidebarWidth,
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
        surveyActiveState: false,
        toggleKey: Math.floor(Math.random() * 10000) + '--key',
        allowOrganizer: true
    };
}

/**
 * Create mutations for StateManager
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
export function createMutations(StateManager) {
    return {
        updateSurveyId: function(newSurveyId) {
            StateManager.set('surveyid', newSurveyId);
        },
        changeLanguage: function(language) {
            StateManager.set('language', language);
        },
        changeCurrentTab: function(value) {
            StateManager.set('currentTab', value);
        },
        changeSidebarwidth: function(value) {
            StateManager.set('sidebarwidth', value);
        },
        maxSideBarWidth: function(value) {
            StateManager.set('maximalSidebar', value);
        },
        changeIsCollapsed: function(value) {
            StateManager.set('isCollapsed', value);
            $(document).trigger('vue-sidemenu-update-link');
        },
        changeMaxHeight: function(newHeight) {
            StateManager.set('maxHeight', newHeight);
        },
        changeSideBarHeight: function(newHeight) {
            StateManager.set('sideBarHeight', newHeight);
        },
        changeInSurveyViewHeight: function(newHeight) {
            StateManager.set('inSurveyViewHeight', newHeight);
        },
        changeSideBodyHeight: function(newHeight) {
            StateManager.set('sideBodyHeight', newHeight ? newHeight + 'px' : '100%');
        },
        changeCurrentUser: function(newUser) {
            StateManager.set('currentUser', newUser);
        },
        closeAllMenus: function() {
            StateManager.set('lastMenuOpen', false);
            StateManager.set('lastMenuItemOpen', false);
            StateManager.set('lastQuestionGroupOpen', false);
            StateManager.set('lastQuestionOpen', false);
        },
        lastMenuItemOpen: function(menuItem) {
            StateManager.set('lastMenuOpen', menuItem.menu_id);
            StateManager.set('lastMenuItemOpen', menuItem.id);
            StateManager.set('lastQuestionGroupOpen', false);
            StateManager.set('lastQuestionOpen', false);
        },
        lastMenuOpen: function(menuObject) {
            StateManager.set('lastMenuOpen', menuObject.id);
            StateManager.set('lastQuestionOpen', false);
            StateManager.set('lastMenuItemOpen', false);
        },
        lastQuestionOpen: function(questionObject) {
            StateManager.set('lastQuestionGroupOpen', questionObject.gid);
            StateManager.set('lastQuestionOpen', questionObject.qid);
            StateManager.set('lastMenuItemOpen', false);
        },
        lastQuestionGroupOpen: function(questionGroupObject) {
            StateManager.set('lastQuestionGroupOpen', questionGroupObject.gid);
            StateManager.set('lastQuestionOpen', false);
        },
        questionGroupOpenArray: function(questionGroupOpenArray) {
            StateManager.set('questionGroupOpenArray', questionGroupOpenArray);
        },
        updateQuestiongroups: function(questiongroups) {
            StateManager.set('questiongroups', questiongroups);
        },
        addToQuestionGroupOpenArray: function(questiongroupToAdd) {
            const state = StateManager.get();
            const tmpArray = state.questionGroupOpenArray.slice();
            tmpArray.push(questiongroupToAdd.gid);
            StateManager.set('questionGroupOpenArray', tmpArray);
        },
        updateSidemenus: function(sidemenus) {
            StateManager.set('sidemenus', sidemenus);
        },
        updateCollapsedmenus: function(collapsedmenus) {
            StateManager.set('collapsedmenus', collapsedmenus);
        },
        updateTopmenus: function(topmenus) {
            StateManager.set('topmenus', topmenus);
        },
        updateBottommenus: function(bottommenus) {
            StateManager.set('bottommenus', bottommenus);
        },
        setSurveyActiveState: function(surveyState) {
            StateManager.set('surveyActiveState', !!surveyState);
        },
        newToggleKey: function() {
            StateManager.set('toggleKey', Math.floor(Math.random() * 10000) + '--key');
        },
        setAllowOrganizer: function(newVal) {
            StateManager.set('allowOrganizer', newVal);
        }
    };
}

/**
 * Create getters for StateManager
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
export function createGetters(StateManager) {
    return {
        substractContainer: function() {
            const state = StateManager.get();
            const bodyWidth = (1 - (parseInt(state.sidebarwidth) / $('#vue-apps-main-container').width())) * 100;
            const collapsedBodyWidth = (1 - (parseInt('98px') / $('#vue-apps-main-container').width())) * 100;
            return Math.floor(state.isCollapsed ? collapsedBodyWidth : bodyWidth) + '%';
        },
        sideBarSize: function() {
            const state = StateManager.get();
            const sidebarWidth = (parseInt(state.sidebarwidth) / $('#vue-apps-main-container').width()) * 100;
            const collapsedSidebarWidth = (parseInt(98) / $('#vue-apps-main-container').width()) * 100;
            return Math.ceil(state.isCollapsed ? collapsedSidebarWidth : sidebarWidth) + '%';
        },
        isRTL: function() {
            return document.getElementsByTagName('html')[0].getAttribute('dir') === 'rtl';
        },
        isCollapsed: function() {
            if (window.innerWidth < 768) {
                return false;
            }
            const state = StateManager.get();
            return state.isCollapsed;
        }
    };
}
