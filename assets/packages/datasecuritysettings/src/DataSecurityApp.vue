<script>
import Mousetrap from 'mousetrap';

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import LanguageSelector from './components/subcomponents/_languageSelector.vue';

import runAjax from './mixins/runAjax.js';

export default {
    name: 'lsnextdataseceditor',
    mixins: [runAjax],
    components: {
        'language-selector' : LanguageSelector,
    },
    data() {
        return {
            datasecmessageEditorObject : ClassicEditor,
            event: null,
            loading: null,
        }
    },
    computed: {
        currentShowsurveypolicynotice: {
            get() { return this.$store.state.showsurveypolicynotice},
            set(newValue) { this.$store.commit('setShowsurveypolicynotice', newValue); }
        },
        currentDataseclabel: {
            get() { return this.$store.state.dataseclabel[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setDataseclabelForCurrentLanguage', newValue); }
        },
        currentDatasecmessage: {
            get() { return this.$store.state.datasecmessage[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setDatasecmessageForCurrentLanguage', newValue); }
        },
    },
    methods: {
        applyHotkeys() {
            Mousetrap.bind('ctrl+right', this.chooseNextLanguage);
            Mousetrap.bind('ctrl+left', this.choosePreviousLanguage);
            Mousetrap.bind('ctrl+shift+s', this.submitCurrentState);
            Mousetrap.bind('ctrl+alt+d', () => {this.$store.commit('toggleDebugMode');});
        },
        chooseNextLanguage() {
            this.$log.log('HOTKEY', 'chooseNextLanguage');
            this.$store.commit('nextLanguage');
        },
        choosePreviousLanguage() {
            this.$log.log('HOTKEY', 'choosePreviousLanguage');
            this.$store.commit('previousLanguage');
        },
        submitCurrentState(redirect = false) {
            this.toggleLoading();
            this.$store.dispatch('saveData').then(
                (result) => {
                    this.toggleLoading();
                    this.$store.state.commit('toggleVisible', true);
                    if(redirect == true) {
                        window.location.href = result.data.redirect;
                    }
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader(result.data.message, 'well-lg bg-primary text-center');
                    this.$log.log('OBJECT AFTER TRANSFER: ', result);
                },
                (reject) => {
                    this.toggleLoading();
                    this.$store.state.commit('toggleVisible', true);
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader("Texts could not be stored. Reloading page.", 'well-lg bg-danger text-center');
                    setTimeout(()=>{window.location.reload();}, 1500);
                }
            )
        },
        selectLanguage(sLanguage) {
            this.$log.log('LANGUAGE CHANGED', sLanguage);
            this.$store.commit('setActiveLanguage', sLanguage);
        }

    },
    created(){
        this.$store.dispatch('loadData');
    },
    
    mounted() {
        $('#advancedDataSecurityTextEditor').on('jquery:trigger', this.jqueryTriggered);
        this.applyHotkeys();

        $('#datasecurity').on('submit', (e)=>{
            e.preventDefault();
        });

        $('#save-button').on('click', (e)=>{
            this.submitCurrentState();
        });

        this.toggleLoading(false);
        
    }
}
</script>

<template>
    <div class="container-center">
        <template v-show="$store.state.visible">
            <div class="row">
                <div class="btn-group" data-toggle=" buttons">
                    <button 
                        class="btn btn-primary" 
                        :class="currentShowsurveypolicynotice == 0?'active':''" 
                        @click.prevent.stop="currentShowsurveypolicynotice=0"
                    >
                        {{"Disabled"|translate}}
                    </button>
                    <button 
                        class="btn btn-primary" 
                        :class="currentShowsurveypolicynotice == 1?'active':''" 
                        @click.prevent.stop="currentShowsurveypolicynotice=1"
                    >
                        {{"Inline text"|translate}}
                    </button>
                    <button 
                        class="btn btn-primary" 
                        :class="currentShowsurveypolicynotice == 2?'active':''" 
                        @click.prevent.stop="currentShowsurveypolicynotice=2"
                    >
                        {{"Collapsible text"|translate}}
                    </button>
                </div>
            </div>
            <div class="row">
                <hr />
            </div>
            <div v-show="$store.state.showsurveypolicynotice > 0" class="cointainer-center">
                <div class="row">
                    <language-selector 
                        :elId="'questioneditor'" 
                        :aLanguages="$store.state.languages" 
                        :parentCurrentLanguage="$store.state.activeLanguage" 
                        @change="selectLanguage"
                    />
                </div>
                <div class="row scoped-editor-row">
                    <div class="col-sm-6 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                        <label for="inputdataseclabel" class="">{{ "Survey data policy checkbox label:" | translate }}:</label>
                        <input type="text" id="inputdataseclabel" class="form-control" v-model="currentDataseclabel"  />
                    </div>
                    <div class="col-sm-6 ls-space margin top-5 bottom-5 ">
                        <div class="col-sm-12 well" v-html="translate('__INFOTEXT')" />
                    </div>
                </div>
                <div class="row scoped-editor-row">
                    <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                        <label class="">{{ "Description" | translate }}:</label>
                        <ckeditor :editor="datasecmessageEditorObject" v-model="currentDatasecmessage" :config="{}"></ckeditor>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<style lang="scss" scoped>
.loading-back-greyed {
    background-color: rgba(200,200,200,0.4);
    width: 100%;
    min-height: 65vh;
    position: absolute;
    top:0;
    left:0;
}

.scoped-editor-row {
    &:before {
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        border-top: 1px solid #dedede;
        width: 95%;
        margin: 0.5rem auto;
        display: block
    }
}
</style>


