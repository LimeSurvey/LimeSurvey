export default {
    substractContainer: state => {
        let bodyWidth = (1 - (parseInt(state.sidebarwidth)/$('#vue-apps-main-container').width()))*100;
        let collapsedBodyWidth = (1 - (parseInt('98px')/$('#vue-apps-main-container').width()))*100;
        return Math.floor(state.isCollapsed ? collapsedBodyWidth : bodyWidth) + '%';
    },
    sideBarSize : state => {
        let sidebarWidth = (parseInt(state.sidebarwidth)/$('#vue-apps-main-container').width())*100;
        let collapsedSidebarWidth = (parseInt(98)/$('#vue-apps-main-container').width())*100;
        return Math.ceil(state.isCollapsed ? collapsedSidebarWidth : sidebarWidth) + '%';
    },
    isRTL: state => {
        return document.getElementsByTagName("html")[0].getAttribute("dir") == 'rtl';
    }
};
