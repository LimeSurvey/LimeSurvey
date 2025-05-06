//globals formId
import Vue from "vue";
import Sidebar from "./components/sidebar.vue";
import getAppState from "./store/vuex-store.js";
import {PluginLog} from "./mixins/logSystem.js";
import Loader from './helperComponents/loader.vue';

//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];
Vue.config.devtools = true;

Vue.use(PluginLog);


Vue.component('loader-widget', Loader);

Vue.mixin({
    methods: {
        updatePjaxLinks: function () {
            this.$forceUpdate();
            this.$store.commit('newToggleKey');
        },
        redoTooltips: function () {
            window.LS.doToolTip();
        },
        translate(string){
            return window.SideMenuData.translate[string] || string;
        }
    },
    filters: {
        translate(string){
            return window.SideMenuData.translate[string] || string;
        }
    }
});

const Lsadminsidepanel = (userid, surveyid) => {
    const AppState = getAppState(userid, surveyid);
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

        panelNameSpace["surveyViewHeight"] = inSurveyViewHeight;
        panelNameSpace["surveyViewWidth"] = inSurveyViewWidth;
        $('#fullbody-container').css({
            //'height': inSurveyViewHeight,
            'max-width': inSurveyViewWidth,
            'overflow-x': 'auto'
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
                this.$store.commit("setAllowOrganizer", window.SideMenuData.allowOrganizer);
                this.updatePjaxLinks();


                $(document).on("vue-redraw", () => {
                    this.updatePjaxLinks();
                });

                $(document).trigger("vue-reload-remote");
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



$(document).ready(function(){
    let surveyid = 'newSurvey';
    if(window.LS != undefined) {
        surveyid = window.LS.parameters.$GET.surveyid || window.LS.parameters.keyValuePairs.surveyid;
    }
    if(window.SideMenuData) {
        surveyid = window.SideMenuData.surveyid;
    }

    window.adminsidepanel =  window.adminsidepanel || Lsadminsidepanel(window.LS.globalUserId, surveyid);

    window.adminsidepanel();
});

