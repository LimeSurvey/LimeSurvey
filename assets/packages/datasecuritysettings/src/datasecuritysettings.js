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
                    $('#questionEditLoader').fadeIn(200);
                } else {
                    $('#questionEditLoader').fadeOut(400);
                }
                return;
            }
            if($('#questionEditLoader').css('display') == 'none') {
                $('#questionEditLoader').fadeIn(200);
                return;
            }
            $('#questionEditLoader').fadeOut(400);
        },
        translate(value) {
            return window.QuestionEditData.i10N[value] || value;
        }
    },
    filters: {
        translate: (value) => {
            return window.QuestionEditData.i10N[value] || value;
        }
    }
});
const AppState = getAppState(window.LS.parameters.qid);
const questionEditor = new Vue({
    el: '#advancedQuestionEditor',
    store: AppState,
    components: {App},
});