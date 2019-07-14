import Vue from 'vue';
import LSCKEditor from '../../meta/LSCKVue/plugin'
import VModal from 'vue-js-modal'

import EmailTemplatesApp from './EmailTemplatesApp.vue';
import Loader from './helperComponents/loader.vue';

import getAppState from "./storage/store";
import {PluginLog} from "./mixins/logSystem";

//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];

Vue.use( PluginLog );
Vue.use( LSCKEditor );
Vue.use(VModal, { dynamic: true });

Vue.component('loader-widget', Loader);

Vue.mixin({
    methods: {
        toggleLoading(forceState=null) {
            this.loading = !this.loading;
            if(forceState !== null) {
                this.loading = forceState;
                if(forceState) {
                    $('#emailtemplatesLoader').fadeIn(200);
                } else {
                    $('#emailtemplatesLoader').fadeOut(400);
                }
                return;
            }
            if($('#emailtemplatesLoader').css('display') == 'none') {
                $('#emailtemplatesLoader').fadeIn(200);
                return;
            }
            $('#emailtemplatesLoader').fadeOut(400);
        },
        translate(value) {
            return window.EmailTemplateData.i10N[value] || value;
        }
    },
    filters: {
        translate: (value) => {
            return window.EmailTemplateData.i10N[value] || value;
        }
    }
});
const CreateEmailTemplateEditor = function(){
    const TextElementsStore = getAppState(LS.parameters.surveyid || 0);
    return new Vue({
        el: '#emailTemplatesEditor',
        store: TextElementsStore,
        components: {'emailtemplateseditor': EmailTemplatesApp},
    });
};

const newEmailTemplateEditor = CreateEmailTemplateEditor();