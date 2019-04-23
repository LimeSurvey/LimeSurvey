import Vue from 'vue';
import CKEditor from '@ckeditor/ckeditor5-vue';


import App from './App.vue';

import getAppState from "./storage/store";
import {PluginLog} from "./mixins/logSystem";


//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];

Vue.use( PluginLog );
Vue.use( CKEditor );

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
const AppState = getAppState(window.QuestionGroupEditData.qid);
const questionEditor = new Vue({
    el: '#advancedQuestionGroupEditor',
    store: AppState,
    components: {'lsnextquestiongroupeditor': App},
});