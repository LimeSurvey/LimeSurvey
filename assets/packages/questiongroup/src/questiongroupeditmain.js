import Vue from 'vue';
import LSCKEditor from '../../meta/LSCKVue/plugin'
import App from './App.vue';
import getAppState from "./storage/store";
import {PluginLog} from "./mixins/logSystem";

import Loader from './helperComponents/loader.vue';

Vue.config.devtools = false;

//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];

Vue.use( PluginLog );
Vue.use( LSCKEditor );

Vue.component('loader-widget', Loader);

Vue.mixin({
    methods: {
        toggleLoading(forceState=null) {
            if(forceState !== null) {
                if(forceState) {
                    $('#questionGroupEditLoader').fadeIn(200);
                } else {
                    $('#questionGroupEditLoader').fadeOut(400);
                }
                return;
            }
            if($('#questionGroupEditLoader').css('display') == 'none') {
                $('#questionGroupEditLoader').fadeIn(200);
                return;
            }
            $('#questionGroupEditLoader').fadeOut(400);
        },
        translate(value) {
            return window.QuestionGroupEditData.i10N[value] || value;
        }
    },
    filters: {
        translate: (value) => {
            return window.QuestionGroupEditData.i10N[value] || value;
        }
    }
});
const AppState = getAppState(window.QuestionGroupEditData.gid);
const questionEditor = new Vue({
    el: '#advancedQuestionGroupEditor',
    store: AppState,
    components: {'lsnextquestiongroupeditor': App},
});
