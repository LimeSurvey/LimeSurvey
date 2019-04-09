import Vue from 'vue';
import CKEditor from '@ckeditor/ckeditor5-vue';

import TextElementsApp from './TextElementsApp.vue';

import getAppState from "./storage/store";
import {PluginLog} from "./mixins/logSystem";

//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];

Vue.use( PluginLog );
Vue.use( CKEditor );

Vue.mixin({
    methods: {
        toggleLoading(forceState=null) {
            this.loading = !this.loading;
            if(forceState !== null) {
                this.loading = forceState;
                if(forceState) {
                    $('#textEditLoader').fadeIn(200);
                } else {
                    $('#textEditLoader').fadeOut(400);
                }
                return;
            }
            if($('#textEditLoader').css('display') == 'none') {
                $('#textEditLoader').fadeIn(200);
                return;
            }
            $('#textEditLoader').fadeOut(400);
        },
        translate(value) {
            return window.DataSecTextEditData.i10N[value] || value;
        }
    },
    filters: {
        translate: (value) => {
            return window.DataSecTextEditData.i10N[value] || value;
        }
    }
});
const TextElementsStore = getAppState(LS.parameters.surveyid);

const newTextEditor = new Vue({
    el: '#advancedTextEditor',
    store: TextElementsStore,
    components: {'lsnexttexteditor': TextElementsApp},
});