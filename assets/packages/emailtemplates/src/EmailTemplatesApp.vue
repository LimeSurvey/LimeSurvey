<script>
import Mousetrap from 'mousetrap';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

import LanguageSelector from './components/subcomponents/_languageSelector.vue';
import Aceeditor from './helperComponents/AceEditor';

import runAjax from './mixins/runAjax.js';

export default {
    name: 'emailtemplateeditor',
    components: {
        'language-selector' : LanguageSelector,
        Aceeditor
    },
    mixins: [runAjax],
    data() {
        return {
            loading: true,
            event: null,
            currentEditor: ClassicEditor,
            sourceMode: false,
        }
    },
    computed: {
        isNewSurvey() {
            return window.EmailTemplateData.isNewSurvey;
        },
        currentSubject: {
            get() { 
                let returner = '';
                try{    
                    if(this.$store.state.templateTypeContents[this.$store.state.activeLanguage]) {
                        let descriptor = this.currentTemplateTypeData.field.subject;
                        returner = this.$store.state.templateTypeContents[this.$store.state.activeLanguage][descriptor];
                    }
                } catch(e) {}
                return returner;
            },
            set(newValue) { this.$store.commit('setSubjectForCurrentState', newValue); },
        },
        currentEditorContent: {
            get() { 
                let returner = '';
                try{    
                    if(this.$store.state.templateTypeContents[this.$store.state.activeLanguage]) {
                        let descriptor = this.currentTemplateTypeData.field.body;
                        returner = this.$store.state.templateTypeContents[this.$store.state.activeLanguage][descriptor];
                    }
                } catch(e) {}
                return returner;
            },
            set(newValue) { this.$store.commit('setEditorContentForCurrentState', newValue); },
        },
        currentTemplateTypeData() {
            return this.$store.state.templateTypes[this.$store.state.currentTemplateType];
        },
        currentTemplateType: {
            get() { return this.$store.state.currentTemplateType; },
            set(newValue) { this.$store.commit('setCurrentTemplateType', newValue); },
        },
        possibletemplateTypes(){ return this.$store.state.templateTypes; },
        languageChangerEnabled() {
            return LS.ld.size(this.$store.state.languages) > 1;
        }
    },
    methods: {
        applyHotkeys() {
            Mousetrap.bind('ctrl+right', this.chooseNextLanguage);
            Mousetrap.bind('ctrl+left', this.choosePreviousLanguage);
            Mousetrap.bind('ctrl+up', this.choosePreviousTemplateType);
            Mousetrap.bind('ctrl+down', this.chooseNextTemplateType);
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
        choosePreviousTemplateType(){
            this.$log.log('HOTKEY', 'previousTemplateType');
            this.$store.commit('previousTemplateType');
        },
        chooseNextTemplateType(){
            this.$log.log('HOTKEY', 'nextTemplateType');
            this.$store.commit('nextTemplateType');
        },
        submitCurrentState(redirect = false) {
            this.toggleLoading();
            this.$store.dispatch('saveData').then(
                (result) => {
                    this.toggleLoading();
                    if(redirect == true) {
                        window.location.href = result.data.redirect;
                    }

                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader(result.data.message, 'well-lg bg-primary text-center');
                    this.$log.log('OBJECT AFTER TRANSFER: ', result);
                },
                (reject) => {
                    this.toggleLoading();
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader("Texts could not be stored. Reloading page.", 'well-lg bg-danger text-center');
                    //setTimeout(()=>{window.location.reload();}, 1500);
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
        resetCurrentContent(){
            this.currentSubject = this.currentTemplateTypeData.subject;
            this.currentEditorContent = this.currentTemplateTypeData.default;
        },
        validateCurrentContent(){}
    },
    created(){
        this.$store.dispatch('getDataSet'). then(
            () => {
                if(this.$store.state.permissions.editorpreset == 'source') {
                    this.sourceMode = true;
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
        $('#emailTemplatesEditor').on('jquery:trigger', this.jqueryTriggered);
        this.applyHotkeys();

        $('#emailtemplates').on('submit', (e)=>{
            e.preventDefault();
        });

        if(!window.EmailTemplateData.isNewSurvey) {
            $('#save-button').on('click', (e)=>{
                this.submitCurrentState();
            });
        }
        this.toggleLoading(false);
    }
}
</script>

<template>
    <div class="container-center scoped-new-emailTemplatesEditor">
        <template v-show="!loading">
            <div class="row" v-if="languageChangerEnabled">
                <language-selector 
                    elId="emailTemplatesEditor" 
                    :aLanguages="$store.state.languages" 
                    :parentCurrentLanguage="$store.state.activeLanguage" 
                    @change="selectLanguage"
                />
            </div>
            <div class="row">
                <hr />
            </div>
            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <div class="scoped-flex-bysize">
                        <div 
                            v-for="(templateType,type) in possibletemplateTypes" 
                            :key="type"
                            class="scoped-tabbable-item btn btn-block btn-default"
                            :class="currentTemplateType==type?'active':''"
                            @click.prevent="currentTemplateType=type"
                        >
                            {{templateType.title|translate}}
                        </div>
                    </div>
                </div>
                <div class="col-md-10 col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="ls-flex-row col-12">
                                <label for="currentSubject" class="">{{currentTemplateTypeData.subject}}:</label>
                            </div>
                            <div v-if="!$store.state.permissions.update" class="col-12" v-html="stripScripts(currentSubject)" />
                            <input class="form-control" v-model="currentSubject" name="currentSubject" id="currentSubject"/>
                        </div>
                        <div class="row">
                            <div class="ls-flex-row col-12">
                                <div class="ls-flex-item text-left">
                                    <label class="">{{currentTemplateTypeData.body}}:</label>
                                </div>
                                <div class="ls-flex-item text-right" v-if="$store.state.permissions.update">
                                    <button class="btn btn-default btn-xs" @click.prevent="sourceMode=!sourceMode"><i class="fa fa-file-code-o"></i>{{'Toggle source mode'|translate}}</button>
                                </div>
                            </div>
                            <div v-if="!$store.state.permissions.update" class="col-12" v-html="stripScripts(currentEditorContent)" />
                            <ckeditor v-if="!sourceMode && $store.state.permissions.update" :editor="currentEditor" v-model="currentEditorContent" :config="{}"></ckeditor>
                            <aceeditor v-if="sourceMode && $store.state.permissions.update" v-model="currentEditorContent" thisId="currentTemplateTypesSourceEditor" :showLangSelector="false"></aceeditor>
                        </div>
                        <div class="row">
                            <div class="ls-flex-row col-12">
                                <button class="btn btn-default" @click.prevent="validateCurrentContent"> {{"Validate Expressions"}} </button>
                                <button class="btn btn-default" @click.prevent="resetCurrentContent"> {{"Reset current"}} </button>
                                <!-- <button class="btn btn-default" @click.prevent="addFileToCurrent"> {{"Add file to current"}} </button> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div v-if="loading"><loader-widget id="emailtemplatesinternalloader" /></div>
    </div>
</template>

<style lang="scss" scoped>

.scope-contains-ckeditor {
    min-height: 10rem;
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
.scoped-flex-bysize {
    flex-direction: row;
    @media(max-width: 767px) {
        flex-direction: column;
    }

    &>div {
        flex: 1;
        border-radius: 0;
        white-space: pre-wrap;
        flex-wrap: wrap;
        border: 1px solid #c3c3c3;
        margin: 0.2rem 0 0.2rem 0.5rem;
        padding: 0.3rem;
        background-color: rgba(196,196,196,0.8);
        &.active {
            background-color: rgba(196,196,196,1);
        }
    }
}
</style>

