import Vue from 'vue';
import LSCKEditor from '../../meta/LSCKVue/plugin'
import VModal from 'vue-js-modal'

import LabelSetApp from './LabelSetApp.vue';
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
        translate(value) {
            return window.LabelSetData.i10N[value] || value;
        }
    },
    filters: {
        translate: (value) => {
            return window.LabelSetData.i10N[value] || value;
        }
    }
});
const CreateLabelSetEditor = function(){
    const LabelSetStoreStore = getAppState(LS.globalUserId);
    return new Vue({
        el: '#labelSetEditor',
        store: LabelSetStoreStore,
        components: {LabelSetApp},
    });
};

const newLabelSetEditor = CreateLabelSetEditor();