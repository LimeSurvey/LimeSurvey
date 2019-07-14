import Vue from 'vue';
import LSCKEditor from '../../meta/LSCKVue/plugin'

import TextElementsApp from './TextElementsApp.vue';
import Loader from './helperComponents/loader.vue';

import getAppState from "./storage/store";
import {PluginLog} from "./mixins/logSystem";

Vue.config.devtools = true;
//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];

Vue.use( PluginLog );
Vue.use( LSCKEditor );

Vue.component('loader-widget', Loader);

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
            return window.TextEditData.i10N[value] || value;
        }
    },
    filters: {
        translate: (value) => {
            return window.TextEditData.i10N[value] || value;
        }
    }
});

const CreateTextElementsEditor = function(){
    const TextElementsStore = getAppState(LS.parameters.surveyid || 0);
    return new Vue({
        el: '#advancedTextEditor',
        store: TextElementsStore,
        components: {'lsnexttexteditor': TextElementsApp},
    });
};

const newTextEditor = CreateTextElementsEditor();