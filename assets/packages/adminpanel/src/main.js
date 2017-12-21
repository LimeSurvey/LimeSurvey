//globals formId
import Vue from 'vue';
import Sidebar from './components/sidebar.vue';
import Topbar from './components/topbar.vue';
import ParameterTable from './components/parameter-table.vue';
import getAppState from './store/vuex-store.js';
import LOG from './mixins/logSystem.js';


//Ignore phpunits testing tags
Vue.config.ignoredElements = ['x-test']

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
                        inSurveyOffset = 230,
                        menuHeight = $('.menubar').outerHeight(),
                        windowHeight = $('html').height(),
                        inSurveyViewHeight = (windowHeight - inSurveyOffset),
                        generalContainerHeight = inSurveyViewHeight - (menuHeight);
                    this.$store.commit('changeInSurveyViewHeight', inSurveyViewHeight);
                    this.$store.commit('changeGeneralContainerHeight', generalContainerHeight);
                }
            },
            beforeCreate() {
                this.controlWindowSize();
            },
            created() {
                this.controlWindowSize();
                window.addEventListener('resize', () => {
                    this.controlWindowSize();
                });

                $(document).on('vue-resize-height',  ()=>{
                    this.controlWindowSize();
                });

                $(document).on('vue-sidebar-collapse',  ()=>{
                    this.$store.commit('changeIsCollapsed', true);
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
                    this.updatePjaxLinks();
                });
                window.singletonPjax();

                $(document).trigger('vue-reload-remote');
                
                window.setInterval(function(){
                    $(document).trigger('vue-reload-remote');
                }, (60*5*1000));
            }
        });
        global.vueGeneralApp = vueGeneralApp;
    }
});

let reloadcounter = 5;

$(document).off('pjax:send.aploading').on('pjax:send.aploading', (e) => {
    $('<div id="pjaxClickInhibitor"></div>').appendTo('body');
    $('.ui-dialog.ui-corner-all.ui-widget.ui-widget-content.ui-front.ui-draggable.ui-resizable').remove();
    $('#pjax-file-load-container').find('div').css({
        'width': '20%',
        'display': 'block'
    });
    reloadcounter--;
});

$(document).off('pjax:error.aploading').on('pjax:error.aploading', (event) => {
    console.log(event);
});

$(document).off('pjax:complete.aploading').on('pjax:complete.aploading', (e) => {
    if(reloadcounter === 0){
        location.reload();
    }
});
$(document).off('pjax:scriptcomplete.aploading').on('pjax:scriptcomplete.aploading', (e) => {
    $('#pjax-file-load-container').find('div').css('width', '100%');
    $('#pjaxClickInhibitor').fadeOut(400, function(){$(this).remove();});     
    $(document).trigger('vue-resize-height');
    $(document).trigger('vue-reload-remote');
    // $(document).trigger('vue-sidemenu-update-link');
    setTimeout(function () {
        $('#pjax-file-load-container').find('div').css({
            'width': '0%',
            'display': 'none'
        });
    }, 2200);
});


// const topmenu = new Vue(
//   {  
//     el: '#vue-top-menu-app',
//     components: {
//       'topbar' : Topbar,
//     } 
// });
