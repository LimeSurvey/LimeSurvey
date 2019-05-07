//globals formId
import Vue from "vue";
Vue.config.devtools = false;

import Sidebar from "./components/sidebar.vue";
import Topbar from "./components/topbar.vue";
import ParameterTable from "./components/parameter-table.vue";
import getAppState from "./store/vuex-store.js";
import {PluginLog} from "./mixins/logSystem.js";

//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];

Vue.use(PluginLog);
Vue.mixin({
    methods: {
        updatePjaxLinks: function () {
            //this.$store.dispatch("updatePjax");
            this.$forceUpdate();
        },
        redoTooltips: function () {
            window.LS.doToolTip();
        }
    }
});

const Lsadminsidepanel = () => {
    const surveyid = $('#vue-apps-main-container').data("surveyid");
    const AppState = getAppState(LS.globalUserId+'-'+surveyid);
    const panelNameSpace = {};

    const applySurveyId = (store) => {
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
                // eslint-disable-next-line no-console
                console.ls.log(event);
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
        window.addEventListener("resize", LS.ld.debounce(controlWindowSize, 300));
        $(document).on("vue-resize-height", LS.ld.debounce(controlWindowSize, 300));
        applyPjaxMethods();

    }

    LS.adminCore.addToNamespace(panelNameSpace, 'adminsidepanel');

    return createPanelAppliance;
};

window.adminsidepanel =  window.adminsidepanel || Lsadminsidepanel();

window.LS.adminCore.appendToLoad(window.adminsidepanel, 'ready');
