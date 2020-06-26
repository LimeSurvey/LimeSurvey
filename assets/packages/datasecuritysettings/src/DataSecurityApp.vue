<script>
import Mousetrap from 'mousetrap';

import LanguageSelector from './components/subcomponents/_languageSelector.vue';
import ClassicEditor from '../../meta/LsCkeditor/src/LsCkEditorClassic.js';
import runAjax from './mixins/runAjax.js';
import Aceeditor from './helperComponents/AceEditor';
import TypeCounter from './helperComponents/TypeCounter.vue'

export default {
    name: 'lsnextdataseceditor',
    mixins: [runAjax],
    components: {
        'language-selector' : LanguageSelector,
        Aceeditor,
        TypeCounter,
    },
    data() {
        return {
            maxDataSecLabelLength: 192,
            datasecmessageEditorObject: ClassicEditor,
            datasecmessageEditorData : {
                'lsExtension:fieldtype': 'survey-datasec', 
                'lsExtension:ajaxOptions': {surveyid: this.$store.getters.surveyid},
                'lsExtension:currentFolder':  'upload/surveys/'+this.$store.getters.surveyid+'/'
            },
            event: null,
            loading: null,
            sourceEditEnable: false,
            loading: true
        }
    },
    computed: {
        isNewSurvey() {return window.DataSecTextEditData.isNewSurvey},
        currentShowsurveypolicynotice: {
            get() { return parseInt(this.$store.state.showsurveypolicynotice)},
            set(newValue) { this.$store.commit('setShowsurveypolicynotice', newValue); }
        },
        currentDataseclabel: {
            get() {
                var value = this.$store.state.dataseclabel[this.$store.state.activeLanguage];
                return value === null ? '' : value;
            },
            set(newValue) { this.$store.commit('setDataseclabelForCurrentLanguage', newValue); }
        },
        currentDatasecmessage: {
            get() { return this.$store.state.datasecmessage[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setDatasecmessageForCurrentLanguage', newValue); }
        },
        languageChangerEnabled() {
            return LS.ld.size(this.$store.state.languages) > 1;
        },
        textTooLong() {
            return this.currentDataseclabel.length > this.maxDataSecLabelLength;
        },
        inputValid() {
            return !this.textTooLong;
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
                    this.$store.commit('toggleVisible', true);
                    if(redirect == true) {
                        window.location.href = result.data.redirect;
                    }
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader(result.data.message, 'well-lg text-center ' + (result.data.success ? 'bg-primary' : 'bg-danger'));
                    this.$log.log('OBJECT AFTER TRANSFER: ', result);
                },
                (reject) => {
                    this.toggleLoading();
                    this.$store.commit('toggleVisible', true);
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader("Texts could not be stored. Reloading page.", 'well-lg bg-danger text-center');
                    setTimeout(()=>{window.location.reload();}, 1500);
                }
            )
        },
        selectLanguage(sLanguage) {
            this.$log.log('LANGUAGE CHANGED', sLanguage);
            this.$store.commit('setActiveLanguage', sLanguage);
        },
        stripScripts(s) {
            const div = document.createElement('div');
            div.innerHTML = s;
            const scripts = div.getElementsByTagName('script');
            let i = scripts.length;
            while (i--) {
                let scriptContent = scripts[i].innerHTML;
                let cleanScript = document.createElement('pre');
                cleanScript.innerHTML = `[script]
${scriptContent}
[/script]`;
                scripts[i].parentNode.appendChild(cleanScript);
                scripts[i].parentNode.removeChild(scripts[i]);
            }
            return div.innerHTML;
        },

    },
    created(){
        this.$store.dispatch('loadData').then(
            () => {
                if(this.$store.state.permissions.editorpreset == 'source') {
                    this.sourceEditEnable = true;
                }
                this.loading = false;
            },
            (error) => {
                this.$log.error(error);
                this.loading = false;
            }
        );
    },

    mounted() {
        $('#advancedDataSecurityTextEditor').on('jquery:trigger', this.jqueryTriggered);
        this.applyHotkeys();

        $('#datasecurity').find('[type="submit"]:not(.ck)').first().on('click', (e)=>{
            e.preventDefault();
            this.submitCurrentState();
        });

        this.toggleLoading(false);
        
    }
}
</script>

<template>
    <div class="container-center">
        <template v-if="!loading">
            <div class="row">
                <div class="btn-group" data-toggle=" buttons">
                    <button 
                        class="btn btn-primary" 
                        :class="currentShowsurveypolicynotice == 0?'active':''" 
                        @click.prevent="currentShowsurveypolicynotice=0"
                    >
                        {{"Disabled"|translate}}
                    </button>
                    <button 
                        class="btn btn-primary" 
                        :class="currentShowsurveypolicynotice == 1?'active':''" 
                        @click.prevent="currentShowsurveypolicynotice=1"
                    >
                        {{"Inline text"|translate}}
                    </button>
                    <button 
                        class="btn btn-primary" 
                        :class="currentShowsurveypolicynotice == 2?'active':''" 
                        @click.prevent="currentShowsurveypolicynotice=2"
                    >
                        {{"Collapsible text"|translate}}
                    </button>
                </div>
            </div>
            <div class="row">
                <hr />
            </div>
            <div v-show="currentShowsurveypolicynotice > 0" class="cointainer-center">
                <div class="row" v-if="languageChangerEnabled">
                    <language-selector 
                        :elId="'questioneditor'" 
                        :aLanguages="$store.state.languages" 
                        :parentCurrentLanguage="$store.state.activeLanguage" 
                        @change="selectLanguage"
                    />
                </div>
                <div class="row scoped-editor-row">
                    <div class="col-sm-6 ls-space margin top-5 bottom-5 scope-contains-ckeditor">
                        <label for="inputdataseclabel" class="">{{ "Survey data policy checkbox label:" | translate }}:</label>
                        <div class="scoped-keep-in-line">
                            <input
                                type="text"
                                id="inputdataseclabel" 
                                name="surveyls_policy_notice_label" 
                                :maxlength="this.maxDataSecLabelLength" 
                                class="form-control has-counter" 
                                v-model="currentDataseclabel"  
                            />
                            <type-counter 
                                :countable="currentDataseclabel.length"
                                :max-value="this.maxDataSecLabelLength"
                                :valid="inputValid"
                            />
                        </div>
                    </div>
                    <div class="col-sm-6 ls-space margin top-5 bottom-5 ">
                        <div class="col-sm-12 well" v-html="translate('__INFOTEXT')" />
                    </div>
                </div>
                <div class="row scoped-editor-row">
                    <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                        <div class="ls-flex-row col-12">
                            <div class="ls-flex-item text-left">
                                <label class="">{{ "Description" | translate }}:</label>
                            </div>
                            <div class="ls-flex-item text-right" v-if="$store.state.permissions.update">
                                <button class="btn btn-default btn-xs" @click.prevent="sourceEditEnable=!sourceEditEnable"><i class="fa fa-file-code-o"></i>{{'Toggle source mode'|translate}}</button>
                            </div>
                        </div>
                        <div v-if="!$store.state.permissions.update" class="col-12" v-html="stripScripts(currentDatasecmessage)" />
                        <lsckeditor :editor="datasecmessageEditorObject" v-if="!sourceEditEnable && $store.state.permissions.update" v-model="currentDatasecmessage" :config="datasecmessageEditorData"></lsckeditor>
                        <aceeditor v-if="sourceEditEnable && $store.state.permissions.update" thisID="datasecmessageSourceEditor" v-model="currentDatasecmessage" :showLangSelector="false"></aceeditor>
                        <input v-if="$store.state.permissions.update" type="hidden" name="surveyls_policy_notice" v-model="currentDatasecmessage"/>
                    </div>
                </div>
            </div>
        </template>
        <div v-if="loading"><loader-widget id="textelementsloader" /></div>
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

.scoped-keep-in-line {
    display: block;
    white-space: nowrap;
    position: relative;
}

input.has-counter {
    padding-right: 36px;
}
</style>


