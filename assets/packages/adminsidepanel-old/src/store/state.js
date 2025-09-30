export default  function(userid) {
    // we want 25% default when screenwidth > 1400px, else: 33%
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
        toggleKey: Math.floor(Math.random()*10000)+'--key',
        allowOrganizer: true
    };
};
