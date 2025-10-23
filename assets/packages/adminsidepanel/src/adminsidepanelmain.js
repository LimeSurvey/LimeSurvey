"use strict"

// Simple state management to replace Vuex
const createStateManager = (userid, surveyid) => {
    const state = {
        userid: userid,
        surveyid: surveyid || 'newSurvey',
        isCollapsed: false,
        maxHeight: 400,
        allowOrganizer: false,
        sideBarHeight: 400,
        currentTab: 'settings',
        sidemenus: [],
        collapsedmenus: [],
        questiongroups: [],
        toggleKey: 0
    };

    const listeners = [];

    return {
        state: state,

        commit(mutation, payload) {
            switch(mutation) {
                case 'updateSurveyId':
                    state.surveyid = payload;
                    break;
                case 'changeIsCollapsed':
                    state.isCollapsed = payload;
                    break;
                case 'changeMaxHeight':
                    state.maxHeight = payload;
                    break;
                case 'setAllowOrganizer':
                    state.allowOrganizer = payload;
                    break;
                case 'changeSideBarHeight':
                    state.sideBarHeight = payload;
                    break;
                case 'changeCurrentTab':
                    state.currentTab = payload;
                    break;
                case 'updateSidemenus':
                    state.sidemenus = payload;
                    break;
                case 'updateCollapsedmenus':
                    state.collapsedmenus = payload;
                    break;
                case 'newToggleKey':
                    state.toggleKey++;
                    break;
            }
            this.notify();
        },

        getters: {
            isCollapsed: () => state.isCollapsed
        },

        notify() {
            listeners.forEach(listener => listener(state));
        },

        subscribe(listener) {
            listeners.push(listener);
            return () => {
                const index = listeners.indexOf(listener);
                if (index > -1) listeners.splice(index, 1);
            };
        }
    };
};

// Translation helper
const translate = (string) => {
    return window.SideMenuData?.translate?.[string] || string;
};

// Tooltip helper
const redoTooltips = () => {
    if (window.LS?.doToolTip) {
        window.LS.doToolTip();
    }
};

const Lsadminsidepanel = (userid, surveyid) => {
    const AppState = createStateManager(userid, surveyid);
    const panelNameSpace = {};

    const applySurveyId = (store) => {
        if (surveyid != 0) {
            store.commit("updateSurveyId", surveyid);
        }
    };

    const controlWindowSize = () => {
        const adminmenuHeight = $("body").find("nav").first().height() || 0;
        const footerHeight = $("body").find("footer").last().height() || 0;
        const menuHeight = $(".menubar").outerHeight() || 0;
        const inSurveyOffset = adminmenuHeight + footerHeight + menuHeight + 25;
        const windowHeight = window.innerHeight;
        const inSurveyViewHeight = windowHeight - inSurveyOffset;

        const $sidebar = $('#sidebar');
        const $mainContainer = $('#vue-apps-main-container');

        if ($sidebar.length && $mainContainer.length) {
            const sidebarWidth = parseInt($sidebar.width()) || 315;
            const mainWidth = $mainContainer.width() || 1;
            const bodyWidth = (1 - (sidebarWidth / mainWidth)) * 100;
            const collapsedBodyWidth = (1 - (98 / mainWidth)) * 100;
            const isCollapsed = $sidebar.data('collapsed');
            const inSurveyViewWidth = Math.floor(isCollapsed ? bodyWidth : collapsedBodyWidth) + '%';

            panelNameSpace["surveyViewHeight"] = inSurveyViewHeight;
            panelNameSpace["surveyViewWidth"] = inSurveyViewWidth;

            $('#fullbody-container').css({
                'max-width': inSurveyViewWidth,
                'overflow-x': 'auto'
            });
        }
    };

    const initializeSideMenu = () => {
        const $container = $("#vue-sidebar-container");

        if (!$container.length) {
            return null;
        }

        // Apply survey ID
        applySurveyId(AppState);

        // Set max height
        const maxHeight = $("#in_survey_common").height() - 35 || 400;
        AppState.commit("changeMaxHeight", maxHeight);

        // Set allow organizer
        if (window.SideMenuData?.allowOrganizer) {
            AppState.commit("setAllowOrganizer", window.SideMenuData.allowOrganizer);
        }

        // Setup event handlers
        $(document).on("vue-sidebar-collapse", () => {
            AppState.commit("changeIsCollapsed", true);
            controlWindowSize();
        });

        $(document).on("vue-redraw", () => {
            redoTooltips();
            AppState.commit('newToggleKey');
        });

        // Initial updates
        redoTooltips();
        $(document).trigger("vue-reload-remote");

        return {
            store: AppState,
            updatePjaxLinks: () => {
                redoTooltips();
                AppState.commit('newToggleKey');
            }
        };
    };

    const applyPjaxMethods = () => {
        panelNameSpace.reloadcounter = 5;

        $(document)
            .off("pjax:send.panelloading")
            .on("pjax:send.panelloading", () => {
                $('<div id="pjaxClickInhibitor"></div>').appendTo("body");
                $(
                    ".ui-dialog.ui-corner-all.ui-widget.ui-widget-content.ui-front.ui-draggable.ui-resizable"
                ).remove();
                $("#pjax-file-load-container")
                    .find("div")
                    .css({
                        width: "20%",
                        display: "block"
                    });
                LS.adminsidepanel.reloadcounter--;
            });

        $(document)
            .off("pjax:error.panelloading")
            .on("pjax:error.panelloading", event => {
                if (console.ls?.log) {
                    console.ls.log(event);
                } else {
                    console.error(event);
                }
            });

        $(document)
            .off("pjax:complete.panelloading")
            .on("pjax:complete.panelloading", () => {
                if (LS.adminsidepanel.reloadcounter === 0) {
                    location.reload();
                }
            });

        $(document)
            .off("pjax:scriptcomplete.panelloading")
            .on("pjax:scriptcomplete.panelloading", () => {
                $("#pjax-file-load-container")
                    .find("div")
                    .css("width", "100%");
                $("#pjaxClickInhibitor").fadeOut(400, function () {
                    $(this).remove();
                });
                $(document).trigger("vue-resize-height");
                $(document).trigger("vue-reload-remote");

                setTimeout(function () {
                    $("#pjax-file-load-container")
                        .find("div")
                        .css({
                            width: "0%",
                            display: "none"
                        });
                }, 2200);
            });
    };

    const createPanelAppliance = () => {
        if (window.singletonPjax) {
            window.singletonPjax();
        }

        if (document.getElementById("vue-sidebar-container")) {
            panelNameSpace['sidemenu'] = initializeSideMenu();
        }

        $(document).on("click", "ul.pagination>li>a", () => {
            $(document).trigger('pjax:refresh');
        });

        controlWindowSize();

        if (window.LS?.ld?.debounce) {
            window.addEventListener("resize", LS.ld.debounce(controlWindowSize, 300));
            $(document).on("vue-resize-height", LS.ld.debounce(controlWindowSize, 300));
        } else {
            // Fallback if lodash debounce is not available
            let resizeTimeout;
            const debouncedResize = () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(controlWindowSize, 300);
            };
            window.addEventListener("resize", debouncedResize);
            $(document).on("vue-resize-height", debouncedResize);
        }

        applyPjaxMethods();
    };

    if (window.LS?.adminCore?.addToNamespace) {
        LS.adminCore.addToNamespace(panelNameSpace, 'adminsidepanel');
    }

    return createPanelAppliance;
};

$(document).ready(function(){
    let surveyid = 'newSurvey';

    if (window.LS !== undefined) {
        surveyid = window.LS.parameters?.$GET?.surveyid || window.LS.parameters?.keyValuePairs?.surveyid || surveyid;
    }

    if (window.SideMenuData?.surveyid) {
        surveyid = window.SideMenuData.surveyid;
    }

    const userId = window.LS?.globalUserId || 0;
    window.adminsidepanel = window.adminsidepanel || Lsadminsidepanel(userId, surveyid);

    window.adminsidepanel();
});
