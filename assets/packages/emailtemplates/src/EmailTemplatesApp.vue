<template>
    <div class="container-center scoped-new-emailTemplatesEditor">
        <x-test id="action::surveyEmailTemplates"></x-test>
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
                    <div class="scoped-flex-bysize" id="emailtemplates--type-select-sidebar">
                        <div 
                            v-for="(templateType,type) in possibletemplateTypes" 
                            :key="type"
                            class="scoped-tabbable-item btn btn-block btn-default"
                            :class="currentTemplateType==type?'active':''"
                            @click.prevent="setTemplateType(type)"
                        >
                            {{templateType.title|translate}}
                        </div>
                    </div>
                </div>
                <div class="col-md-10 col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor" id="emailtemplates--type-select-editorbody">
                    <div class="container-fluid"> 
                        <div class="row ls-space margin top-5">
                            <div class="ls-flex-row col-12">
                                <label for="currentSubject" class="">{{currentTemplateTypeData.subject || ''}}</label>
                            </div>
                            <div v-if="!$store.state.permissions.update" class="col-12" v-html="stripScripts(currentSubject)" />
                            <input class="form-control" v-model="currentSubject" name="currentSubject" id="currentSubject"/>
                        </div>
                        <div class="row ls-space margin top-15 ckedit-nocollapse" id="EmailTemplates--editor-container">
                            <div class="ls-flex-row col-12">
                                <div class="ls-flex-item text-left">
                                    <label class="">{{currentTemplateTypeData.body}}</label>
                                </div>
                                <div class="ls-flex-item text-right" v-if="$store.state.permissions.update">
                                    <button class="btn btn-default btn-xs" @click.prevent="sourceMode=!sourceMode"><i class="fa fa-file-code-o"></i>{{'Toggle source mode'|translate}}</button>
                                </div>
                            </div>
                            <div v-if="!$store.state.permissions.update" class="col-12" v-html="stripScripts(currentEditorContent)" />
                            <lsckeditor v-if="!sourceMode && $store.state.permissions.update" :editor="currentEditorObject" v-model="currentEditorContent" :config="currentEditorOptions" :extra-data="editorExtraOptions" @ready="onReadySetEditor"></lsckeditor>
                            <aceeditor v-if="sourceMode && $store.state.permissions.update" @external-change-applied="applyExternalChange=false" :apply-external-change="applyExternalChange" v-model="currentEditorContent" thisId="currentTemplateTypesSourceEditor" :showLangSelector="false"></aceeditor>
                        </div>
                        <div class="row ls-space margin top-15">
                            <div class="ls-flex-row col-12">
                                <button id="EmailTemplates--actionbutton-validateCurrentContent" class="btn btn-default" @click.prevent="validateCurrentContent"> {{"Validate ExpressionScript"}} </button>
                                <button id="EmailTemplates--actionbutton-resetCurrentContent" class="btn btn-default" @click.prevent="resetCurrentContent"> {{"Reset to default"}} </button>
                                <button id="EmailTemplates--actionbutton-addFileToCurrent" class="btn btn-default" @click.prevent="addFileToCurrent"> {{"Add attachment to template"}} </button>
                            </div>
                        </div>
                        <div class="row ls-space margin top-15" v-if="hasAttachments">
                            <div class="scoped-simple-carousel ls-flex-row">
                                <div 
                                    v-for="file in currentAttachments"
                                    :key="file.hash"
                                    class="simple-carousel-item"
                                >
                                    <i class="fa fa-times text-danger simple-carousel-delete" @click="deleteAttachment(file)" />
                                    <img v-if="file.isImage !='false'" class="scoped-contain-image" :src="file.src" :alt="file.shortName" />
                                    <i v-else :class="'fa '+file.iconClass+' fa-4x'"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div v-if="loading">
            <loader-widget id="emailtemplatesinternalloader" />
        </div>
        <modals-container />
    </div>
</template>

<script>
import Mousetrap from 'mousetrap';
import he from 'he';

import ValidationScreen from './components/ValidationScreen';
import LanguageSelector from './components/subcomponents/_languageSelector';
import InlineEditor from '../../meta/LsCkeditor/src/LsCkEditorInline.js';
import Aceeditor from './helperComponents/AceEditor';

import FileSelectModal from './components/FileSelectModal';
import runAjax from './mixins/runAjax';

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
            editorInstance: null,
            currentEditorObject: InlineEditor,
            currentEditorOptions : {
                'lsExtension:fieldtype': 'email_general', 
                'lsExtension:ajaxOptions': {surveyid: this.$store.getters.surveyid },
                'lsExtension:currentFolder':  'upload/surveys/'+this.$store.getters.surveyid+'/'
            },
            sourceMode: false,
            applyExternalChange: true
        }
    },
    computed: {
        isNewSurvey() {
            return window.EmailTemplateData.isNewSurvey;
        },
        currentAttachments: {
            get() {
                if(this.$store.state.templateTypeContents[this.$store.state.activeLanguage].attachments != null) {
                    return this.$store.state.templateTypeContents[this.$store.state.activeLanguage]
                            .attachments[this.$store.state.currentTemplateType];
                }
                return null;
            },
            set(newVal) {
                this.$store.commit('setAttachementForTypeAndLanguage', newVal);
            }
        },
        hasAttachments() {
            return this.currentAttachments != null;
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
            set(newValue) { 
                if (newValue.subject != undefined) {
                    this.$store.commit('setSubjectForCurrentState', newValue.subject); 
                } else {
                    this.$store.commit('setSubjectForCurrentState', newValue); 
                }
            },
        },
        editorExtraOptions() { 
            return {'fieldtype': 'email_'+this.$store.state.currentTemplateType };
        },
        currentEditorContent: {
            get() { 
                let returner = '';
                try{    
                    if (this.$store.state.templateTypeContents[this.$store.state.activeLanguage]) {
                        let descriptor = this.currentTemplateTypeData.field.body;
                        if(!this.$store.state.useHtml) {
                            returner = this.nl2br(he.decode(this.$store.state.templateTypeContents[this.$store.state.activeLanguage][descriptor]));
                        } else {
                            returner = this.$store.state.templateTypeContents[this.$store.state.activeLanguage][descriptor];
                        }
                    }
                } catch(e) {}
                return returner;
            },
            set(newValue) { 
                if (newValue.body) {
                    this.$store.commit('setEditorContentForCurrentState', newValue.body); 
                } else {
                    this.$store.commit('setEditorContentForCurrentState', newValue); 
                }
            },
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
        },
    },
    methods: {
        nl2br (str, is_xhtml=true) {
            if (typeof str === 'undefined' || str === null) {
                return '';
            }
            var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
            return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
        },
        onReadySetEditor(editor) {
            this.editorInstance = editor;
        },
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
            if (this.editorInstance != null) { 
                this.editorInstance.set('fieldtype', 'email_'+this.currentTemplateType);
            }
        },
        chooseNextTemplateType(){
            this.$log.log('HOTKEY', 'nextTemplateType');
            this.$store.commit('nextTemplateType');
            if (this.editorInstance != null) { 
                this.editorInstance.set('fieldtype', 'email_'+this.currentTemplateType);
            }
        },
        submitCurrentState(redirect = false) {
            this.loading = true;
            this.$store.dispatch('saveData').then(
                (result) => {
                    if(redirect == true) {
                        window.location.href = result.data.redirect;
                    }

                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader(result.data.message, 'well-lg bg-primary text-center');
                    this.$log.log('OBJECT AFTER TRANSFER: ', result);
                },
                (reject) => {
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader("Texts could not be stored. Reloading page.", 'well-lg bg-danger text-center');
                    //setTimeout(()=>{window.location.reload();}, 1500);
                }
            ).finally(() => { this.loading = false; })
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
        validateCurrentContent(){
            this.$modal.show(ValidationScreen, {
              }, {
                width: '75%',
                height: '75%',
                scrollable: true,
              }
            );
        },
        setTemplateType(type) {
            this.applyExternalChange=true;
            this.currentTemplateType=type;
            if (this.editorInstance != null) { 
                this.editorInstance.set('fieldtype', 'email_'+this.currentTemplateType);
            }
        },
        addFileToCurrent() {
            this.$modal.show(
                FileSelectModal,
                {},
                {
                    width: '75%',
                    height: '75%',
                    scrollable: true,
                    resizable: false
                },
            );
        },
        deleteAttachment(file) {
            this.currentAttachments = LS.ld.filter(this.currentAttachments, (att) => att.hash != file.hash );
        }
    },
    created(){
        this.$store.dispatch('getDataSet'). then(
            () => {
                if(this.$store.state.permissions.editorpreset == 'source') {
                    this.sourceMode = true;
                }
                this.loading = false;
                this.applyExternalChange = false;
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
           LS.EventBus.$on('componentFormSubmit', () => {
                this.submitCurrentState();
            });
        }
        this.loading = false;
    }
}
</script>

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
        flex-wrap: wrap;
        padding: 0.5rem 0.3rem;
        &.active {
            font-weight:bold;
        }
    }
}
.scoped-simple-carousel {
    overflow-x: scroll;
    overflow-y: hidden;
    white-space: nowrap;
    .simple-carousel-item {
        width: 23%;
        margin: 1%;
        height: 6.5em;
        box-shadow: 1px 3px 5px #cfcfcf;
        display: inline-flex;
        align-content: center;
        position: relative;
        &>.simple-carousel-delete {
            position: absolute;
            top: 2px;
            right: 2px;
        }
        &>.scoped-contain-image {
            max-width: 100%;
            max-height: 5em;
            display: block;
            margin: auto;
        }
        &>i.fa {
            max-height: 5em;
            display: block;
            margin: auto;
        }
    }
}
</style>

