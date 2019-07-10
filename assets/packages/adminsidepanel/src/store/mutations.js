export default {
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
        let tmpArray = state.questionGroupOpenArray;
        tmpArray.push(questiongroupToAdd.gid);
        state.questionGroupOpenArray = tmpArray;
    },
    updateSidemenus(state, sidemenus) {
        state.sidemenus = sidemenus;
    },
    updateCollapsedmenus(state, collapsedmenus) {
        state.collapsedmenus = collapsedmenus;
    },
    updateTopmenus(state, topmenus) {
        state.topmenus = topmenus;
    },
    updateBottommenus(state, bottommenus) {
        state.bottommenus = bottommenus;
    },
    setSurveyActiveState(state, surveyState) {
        state.surveyActiveState = !!surveyState;
    },
    newToggleKey(state){
        state.toggleKey = Math.floor(Math.random()*10000)+'--key';
    }
};