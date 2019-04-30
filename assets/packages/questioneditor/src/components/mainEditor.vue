<script>

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import debounce from 'lodash/debounce';
import isEqual from 'lodash/isEqual';
import merge from 'lodash/merge';

import Aceeditor from '../helperComponents/AceEditor';

import PreviewFrame from './subcomponents/_previewFrame';
import runAjax from '../mixins/runAjax';
import eventChild from '../mixins/eventChild';

export default {
    name: 'MainEditor',
    mixins: [runAjax, eventChild],
    components: {PreviewFrame, Aceeditor},
    data() {
        return {
            editorQuestion: ClassicEditor,
            editorQuestionData: '',
            editorQuestionConfig: {},
            editorHelp: ClassicEditor,
            editorHelpData: '',
            editorHelpConfig: {},
            previewContent: ' ',
            questionEditSource: false,
            helpEditSource: false,
            previewLoading: false,
            previewActive: true,
            debug: false,
            firstStart: true,
            changeTriggered: debounce((content,event) => {
                this.$log.log('Debounced load triggered',{content,event});
                this.getQuestionPreview();
            }, 3000),
        };
    },
    computed: {
        previewRootUrl() {
            return window.QuestionEditData.qid != null 
            ? [
                window.QuestionEditData.connectorBaseUrl,
                '/getRenderedPreview/iQuestionId/',
                window.QuestionEditData.qid,
                (this.firstStart ? '/root/1' : ''),
                '/sLanguage/',
                this.$store.state.activeLanguage].join('')
            : 'about:blank';
        },
        currentQuestionQuestion: { 
            get() {return this.$store.state.currentQuestionI10N[this.$store.state.activeLanguage].question; },
            set(newValue) {
                this.$store.commit('updateCurrentQuestionI10NValue', {value:'question', newValue});
            } 
        },
        currentQuestionHelp: {
            get() {return this.$store.state.currentQuestionI10N[this.$store.state.activeLanguage].help },
            set(newValue) {
                this.$store.commit('updateCurrentQuestionI10NValue', {value:'help', newValue})
            } 
        },
        currentQuestionScript: {
            get() {return this.$store.state.currentQuestionI10N[this.$store.state.activeLanguage].script },
            set(newValue) {
                this.$store.commit('updateCurrentQuestionI10NValue', {value:'script', newValue})
            } 
        },
        currentQuestionI10N() {
            return this.$store.state.currentQuestionI10N[this.$store.state.activeLanguage];
        },
        questionImmutableI10NQuestion() {
            return this.$store.state.questionImmutableI10N[this.$store.state.activeLanguage].question;
        },
        questionImmutableI10NHelp() {
            return this.$store.state.questionImmutableI10N[this.$store.state.activeLanguage].help;
        },
    },
    methods: {
        changedParts() {
            let changed = {};
            this.$log.log('CHANGE!',{
                currentQuestionQuestion: this.currentQuestionQuestion,
                questionImmutableI10NQuestion: this.questionImmutableI10NQuestion,
                currentQuestionHelp: this.currentQuestionHelp,
                questionImmutableI10NHelp: this.questionImmutableI10NHelp,
                'questionEqal' : isEqual(this.currentQuestionQuestion, this.questionImmutableI10NQuestion),
                'helpEqual' : isEqual(this.currentQuestionHelp, this.questionImmutableI10NHelp)
            });
            if(!(
                isEqual(this.currentQuestionQuestion, this.questionImmutableI10NQuestion)
                && isEqual(this.currentQuestionHelp, this.questionImmutableI10NHelp)
            )) {
                changed['changedText'] = this.currentQuestionI10N;
            }
            if(!isEqual(this.$store.state.currentQuestion.type, this.$store.state.questionImmutable.type)) {
                changed['changedType'] = this.$store.state.currentQuestion.type;
            }
            this.$log.log('CHANGEOBJECT',changed);
            
            return merge(changed, window.LS.data.csrfTokenData);
        },
        runDebouncedChange(content,event){
            this.changeTriggered(content,event);
        },
        triggerPreview(){
            this.previewActive=!this.previewActive
            if(this.previewActive) {
                this.getQuestionPreview();
            }
        },
        getQuestionPreview(){
            this.$log.log('window.QuestionEditData.qid', window.QuestionEditData.qid);
            if(!window.QuestionEditData.qid) {
                this.previewContent = `<div><h3>${this.translate('No preview available')}</h3></div>`;
                this.previewLoading = false;
                return;
            }
            if(this.previewLoading === true) {
                return;
            }
            this.firstStart = false;
            this.previewLoading = true;
            this.$_load(
                this.previewRootUrl, 
                this.changedParts(),
                'POST'
            ).then(
                (result) => {
                    this.previewContent = result.data;
                    this.previewLoading = false;
                }, 
                (error) => {
                    this.$log.error('Error loading preview', error);
                    this.previewLoading = false;
                }
            );
        },
        setPreviewReady() {
            this.previewLoading = false;
            this.firstStart = false;
        },
        toggleSourceEditQuestion(){
            this.questionEditSource = !this.questionEditSource
        },
        toggleSourceEditHelp(){
            this.helpEditSource = !this.helpEditSource
        },
    },
    created(){
        if(this.$store.state.currentQuestionPermissions.editorpreset == 'source') {
            this.questionEditSource = true;
            this.helpEditSource = true;
        }
    },
    mounted(){
        this.previewLoading = true;
        this.toggleLoading(false);
    },
}
</script>

<template>
    <div class="col-sm-8 col-xs-12">
        <div class="panel panel-default question-option-general-container">
            <div class="panel-heading">
                {{"Text elements" | translate }}
            </div>
            <div class="panel-body">
                <div class="col-12 ls-space margin all-5 scope-contains-ckeditor ">
                    <div class="ls-flex-row">
                        <div class="ls-flex-item grow-2 text-left">
                            <label class="col-sm-12">{{ 'Question' | translate }}:</label>
                        </div>
                        <div class="ls-flex-item text-right">
                            <button class="btn btn-default btn-xs" @click="toggleSourceEditQuestion"><i class="fa fa-file-code-o"></i>{{'Toggle source mode'|translate}}</button>
                        </div>
                    </div>
                    <ckeditor v-if="!questionEditSource" :editor="editorQuestion" v-model="currentQuestionQuestion" v-on:input="runDebouncedChange" :config="editorQuestionConfig"></ckeditor>
                    <aceeditor v-else :showLangSelector="false" :thisId="'questionEditSource'" v-model="currentQuestionQuestion"> </aceeditor>
                </div>
                <div class="col-12 ls-space margin all-5 scope-contains-ckeditor ">
                    <div class="ls-flex-row">
                        <div class="ls-flex-item grow-2 text-left">
                            <label class="col-sm-12">{{ 'Help' | translate }}:</label>
                        </div>
                        <div class="ls-flex-item text-right">
                            <button class="btn btn-default btn-xs" @click="toggleSourceEditHelp"><i class="fa fa-file-code-o"></i>{{'Toggle source mode'|translate}}</button>
                        </div>
                    </div>
                    <ckeditor v-if="!helpEditSource" :editor="editorHelp" v-model="currentQuestionHelp" v-on:input="runDebouncedChange" :config="editorHelpConfig"></ckeditor>
                    <aceeditor v-else :showLangSelector="false" :thisId="'helpEditSource'" v-model="currentQuestionHelp"> </aceeditor>
                </div>
                <div class="col-12 ls-space margin all-5 scope-contains-ckeditor " v-if="!!$store.state.currentQuestionPermissions.script">
                    <label class="col-sm-12">{{ 'Script' | translate }}:</label>
                    <aceeditor :thisId="'helpEditScript'" :showLangSelector="true" v-model="currentQuestionScript" base-lang="javascript" > </aceeditor>
                    <p class="alert well">{{"__SCRIPTHELP"|translate}}</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 ls-space margin top-5 bottom-5" >
                <hr/>
            </div>
        </div>
        <div class="row"  v-if="$store.state.currentQuestion.qid != null">
            <div class="col-sm-12 ls-space margin bottom-5">
                <button class="btn btn-default pull-right" @click.prevent="triggerPreview">
                    {{previewActive ? "Hide Preview" : "Show Preview"}}
                </button>
            </div>
            <div class="col-sm-12 ls-space margin top-5 bottom-5">
                <div class="scope-preview" v-show="previewActive">
                    <PreviewFrame :id="'previewFrame'" :content="previewContent" :root-url="previewRootUrl" :firstStart="firstStart" @ready="setPreviewReady" :loading="previewLoading" />
                </div>
            </div>
        </div>
    </div>
</template>


<style lang="scss" scoped>
.scope-set-min-height {
    min-height: 40vh;
}
.scope-border-simple {
    border: 1px solid #cfcfcf;
}
.scope-overflow-scroll {
    overflow: scroll;
    height:100%;
    width: 100%;
}
.scope-preview {
    margin: 15px 5px;
    padding: 2rem;
    border: 3px double #dfdfdf;
    min-height: 20vh;
    resize: vertical;
    overflow: auto;
}
.scope-contains-ckeditor {
    min-height: 10rem;
}
</style>
