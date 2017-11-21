//globals formId
import Vue from 'vue';
import Sidebar from './components/sidebar.vue';
import Topbar from './components/topbar.vue';
import ParameterTable from './components/parameter-table.vue';
import getAppState from './store/vuex-store.js';
import LOG from './mixins/logSystem.js';



Vue.use(LOG);

Vue.mixin({
    methods: {
        updatePjaxLinks: function () {
            this.$store.commit('updatePjax');
        }
    }
});

$(document).on('ready', function () {
    const AppState = getAppState(LS.globalUserId);
    if (document.getElementById('vue-app-main-container')) {
        // eslint-disable-next-line
        const vueGeneralApp = new Vue({
            el: '#vue-app-main-container',
            store: AppState,
            components: {
                'sidebar': Sidebar,
                'topbar': Topbar,
                'lspanelparametertable': ParameterTable,
            },
            methods: {
                controlWindowSize() {
                    const
                        menuOffset = $('nav.navbar').outerHeight(),
                        menuHeight = $('.menubar.surveymanagerbar').outerHeight(),
                        footerHeight = $('footer').outerHeight(),
                        windowHeight = Math.max([screen.availHeight, screen.height]),
                        innerMenuHeight = $('#breadcrumb-container').outerHeight(),
                        inSurveyViewHeight = (windowHeight - (menuOffset + (2 * menuHeight) + (2 * footerHeight))),
                        generalContainerHeight = inSurveyViewHeight - (innerMenuHeight);

                    this.$store.commit('changeInSurveyViewHeight', inSurveyViewHeight);
                    this.$store.commit('changeGeneralContainerHeight', generalContainerHeight);
                }
            },
            created() {
                this.controlWindowSize();
                window.addEventListener('resize', () => {
                    this.controlWindowSize();
                });
            },
            mounted() {
                const surveyid = $(this.$el).data('surveyid');
                if (surveyid != 0) {
                    this.$store.commit('updateSurveyId', surveyid);
                }
                const maxHeight = ($('#in_survey_common').height() - 35) || 400;
                this.$store.commit('changeMaxHeight', maxHeight);
                this.updatePjaxLinks();
                $(document).on('click', 'ul.pagination>li>a',  ()=>{
                    this.updatePjaxLinks();
                });
                $(document).on('vue-redraw',  ()=>{
                    this.$forceUpdate();
                });
            }
        });
        global.vueGeneralApp = vueGeneralApp;
    }

    $(document).on('pjax:send', () => {
        $('<div id="pjaxClickInhibitor"></div>').appendTo('body');
        $('.ui-dialog.ui-corner-all.ui-widget.ui-widget-content.ui-front.ui-draggable.ui-resizable').remove();
        $('#pjax-file-load-container').find('div').css({
            'width': '20%',
            'display': 'block'
        });
    });
    $(document).on('pjax:success', () => {
        $('#pjaxClickInhibitor').fadeOut(400, function(){$(this).remove();});
        $('#pjax-file-load-container').find('div').css('width', '100%');
        setTimeout(function () {
            $('#pjax-file-load-container').find('div').css({
                'width': '0%',
                'display': 'none'
            });
        }, 2200);
    });
});

// const topmenu = new Vue(
//   {  
//     el: '#vue-top-menu-app',
//     components: {
//       'topbar' : Topbar,
//     } 
// });
