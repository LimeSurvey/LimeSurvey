import Vue from 'vue';

import MainEditor from './components/mainEditor.vue';
import GeneralSettings from './components/generalSettings.vue';
import AdvancedSettings from './components/advancedSettings.vue';


import getAppState from "./storage/store";
import LOG from "./mixins/logSystem";



// import CKEditor from '@ckeditor/ckeditor5-vue';

//Ignore phpunits testing tags
Vue.config.ignoredElements = ["x-test"];

Vue.use(LOG);

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
    }
});

const questionEditor = new Vue({
    el: '#advancedQuestionEditor',
    store: getAppState(window.LS.parameters.qid),
    components: {
        'maineditor' : MainEditor,
        'generalsettings' : GeneralSettings,
        'advancedsettings' : AdvancedSettings
    },
    mounted(){
        this.toggleLoading(false);
    }
});