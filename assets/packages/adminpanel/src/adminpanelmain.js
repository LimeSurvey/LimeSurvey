//globals formId
import Vue from "vue";
Vue.config.devtools = false;

import Sidebar from "./components/sidebar.vue";
import Topbar from "./components/topbar.vue";
import ParameterTable from "./components/parameter-table.vue";
import getAppState from "./store/vuex-store.js";
import LOG from "./mixins/logSystem.js";

//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];

Vue.use(LOG);
Vue.mixin({
    methods: {
        updatePjaxLinks: function () {
            this.$store.commit("updatePjax");
        },
        redoTooltips: function () {
            window.LS.doToolTip();
        }
    }
});

const LsAdminPanel = () => {
    const AppState = getAppState(LS.globalUserId);
    const panelNameSpace = {};

    const applySurveyId = (store) => {
        const surveyid = $('#vue-apps-main-container').data("surveyid");
        if (surveyid != 0) {
            store.commit("updateSurveyId", surveyid);
        }
    };

    const controlWindowSize = () => {
        const adminmenuHeight = $("body").find("nav").first().height();
        const footerHeight = $("body").find("footer").last().height();
        const menuHeight = $(".menubar").outerHeight();
        const inSurveyOffset = adminmenuHeight + footerHeight + menuHeight + 25;
        const windowHeight = window.innerHeight;
        const inSurveyViewHeight = windowHeight - inSurveyOffset;
        const bodyWidth = (1 - (parseInt($('#sidebar').width()) / $('#vue-apps-main-container').width())) * 100;
        const collapsedBodyWidth = (1 - (parseInt('98px') / $('#vue-apps-main-container').width())) * 100;
        const inSurveyViewWidth = Math.floor($('#sidebar').data('collapsed') ? bodyWidth : collapsedBodyWidth) + '%';
        console.ls.log({
            adminmenuHeight,
            footerHeight,
            menuHeight,
            inSurveyOffset,
            windowHeight,
            inSurveyViewHeight,
            bodyWidth,
            collapsedBodyWidth,
            inSurveyViewWidth
        });
        panelNameSpace["surveyViewHeight"] = inSurveyViewHeight;
        panelNameSpace["surveyViewWidth"] = inSurveyViewWidth;
        $('#pjax-content').css({
            //'height': inSurveyViewHeight,
            'max-width': inSurveyViewWidth,
        });
    }

    const createSideMenu = () => {
        return new Vue({
            el: "#vue-sidebar-container",
            store: AppState,
            components: {
                sidebar: Sidebar,
            },
            created() {
                $(document).on("vue-sidebar-collapse", () => {
                    this.$store.commit("changeIsCollapsed", true);
                });
            },
            mounted() {
                applySurveyId(this.$store);

                const maxHeight = $("#in_survey_common").height() - 35 || 400;
                this.$store.commit("changeMaxHeight", maxHeight);
                this.updatePjaxLinks();


                $(document).on("vue-redraw", () => {
                    this.$forceUpdate();
                    this.updatePjaxLinks();
                });

                $(document).trigger("vue-reload-remote");
                window.setInterval(function () {
                    $(document).trigger("vue-reload-remote");
                }, 60 * 5 * 1000);
            }
        });
    };

    const createParameterTable = () => {
        return new Vue({
            el: "#vue-parameter-table-container",
            store: AppState,
            components: {
                lspanelparametertable: ParameterTable,
            },
            mounted() {
                applySurveyId(this.$store);
            }
        });
    };


    const applyPjaxMethods = () => {

        panelNameSpace.reloadcounter = 5;
        $(document)
            .off("pjax:send.aploading")
            .on("pjax:send.aploading", () => {
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
                LS.adminpanel.reloadcounter--;
            });

        $(document)
            .off("pjax:error.aploading")
            .on("pjax:error.aploading", event => {
                // eslint-disable-next-line no-console
                console.ls.log(event);
            });

        $(document)
            .off("pjax:complete.aploading")
            .on("pjax:complete.aploading", () => {
                if (LS.adminpanel.reloadcounter === 0) {
                    location.reload();
                }
            });
        $(document)
            .off("pjax:scriptcomplete.aploading")
            .on("pjax:scriptcomplete.aploading", () => {
                $("#pjax-file-load-container")
                    .find("div")
                    .css("width", "100%");
                $("#pjaxClickInhibitor").fadeOut(400, function () {
                    $(this).remove();
                });
                $(document).trigger("vue-resize-height");
                $(document).trigger("vue-reload-remote");
                // $(document).trigger('vue-sidemenu-update-link');
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
        window.singletonPjax();
        if (document.getElementById("vue-sidebar-container")) {
            panelNameSpace['sidemenu'] = createSideMenu();
        }
        if (document.getElementById("vue-parameter-table-container")) {
            panelNameSpace['parameterTable'] = createParameterTable();
        }

        $(document).on("click", "ul.pagination>li>a", () => {
            $(document).trigger('pjax:refresh');
        });

        controlWindowSize();
        window.addEventListener("resize", _.debounce(controlWindowSize, 300));
        $(document).on("vue-resize-height", _.debounce(controlWindowSize, 300));
        applyPjaxMethods();

    }

    LS.adminCore.addToNamespace(panelNameSpace, 'adminpanel');

    return createPanelAppliance;
};

window.AdminPanel =  window.AdminPanel || LsAdminPanel();

window.LS.adminCore.appendToLoad(window.AdminPanel);
