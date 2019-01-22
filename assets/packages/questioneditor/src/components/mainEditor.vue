<script>

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import debounce from 'lodash/debounce';
import isEqual from 'lodash/isEqual';
import PreviewFrame from './subcomponents/_previewFrame.vue';
import LanguageSelector from './subcomponents/_languageSelector.vue';
import runAjax from '../mixins/runAjax.js';
import eventChild from '../mixins/eventChild.js';

export default {
    name: 'MainEditor',
    mixins: [runAjax, eventChild],
    components: {PreviewFrame, LanguageSelector},
    data() {
        return {
            currentQuestionCode: '',
            editorQuestion: ClassicEditor,
            editorQuestionData: '',
            editorQuestionConfig: {},
            editorHelp: ClassicEditor,
            editorHelpData: '',
            editorHelpConfig: {},
            previewContent: ' ',
            previewRootUrl: window.QuestionEditData.connectorBaseUrl+'/getRenderedPreview/iQuestionId/'+window.QuestionEditData.qid,
            previewLoading: false,
            previewActive: true,
            debug: false,
            questionEditButton: window.questionEditButton,
            changeTriggered: debounce((content,event) => {
                this.$log.log('Debounced load triggered',{content,event});
                this.getQuestionPreview();
            }, 3000),
        };
    },
    computed: {
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
            
            return changed;
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
        questionTypeChangeTriggered(newValue) {
            this.$log.log('CHANGE OF TYPE', newValue);
            this.currentQuestionType = newValue;
            let tempQuestionObject = this.$store.state.currentQuestion;
            tempQuestionObject.type = newValue;
            this.$store.commit('setCurrentQuestion', tempQuestionObject);
            this.getQuestionPreview();
            this.$store.dispatch('getQuestionGeneralSettingsWithType');
        },
        getQuestionPreview(){
            if(this.previewLoading === true) {
                return;
            }
            this.previewLoading = true;
            this.$_load(
                this.previewRootUrl+'/sLanguage/'+this.$store.state.activeLanguage, 
                this.changedParts(),
                'POST'
            ).then((result) => {
                this.previewContent = result.data;
                this.previewLoading = false;
            });
        },
        selectLanguage(sLanguage) {
            this.$log.log('LANGUAGE CHANGED', sLanguage);
            this.$store.commit('setActiveLanguage', sLanguage);
        }
    },
    mounted(){
        this.getQuestionPreview();
        this.currentQuestionCode = this.$store.state.currentQuestion.title;
    },
}
</script>

<template>
    <div class="col-sm-8 col-xs-12 ls-space padding all-5">
        <div class="container-center">
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="questionCode">{{'Code' | translate }}</label>
                    <input type="text" class="form-control" id="questionCode" v-model="currentQuestionCode">
                </div>
                <div class="form-group col-sm-6 contains-question-selector">
                    <label for="questionCode">{{'Question type' | translate }}</label>
                    <div v-html="questionEditButton" />
                    <input type="hidden" id="question_type" name="type" @change="questionTypeChangeTriggered" :value="$store.state.currentQuestion.type" />
                </div>
            </div>
            <div class="row">
                <language-selector 
                    :elId="'questioneditor'" 
                    :aLanguages="$store.state.languages" 
                    :parentCurrentLanguage="$store.state.activeLanguage" 
                    @change="selectLanguage"
                />
            </div>
            <div class="row">
                <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                    <label class="col-sm-12">{{ 'Question' | translate }}:</label>
                    <ckeditor :editor="editorQuestion" v-model="currentQuestionQuestion" v-on:input="runDebouncedChange" :config="editorQuestionConfig"></ckeditor>
                </div>
                <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                    <label class="col-sm-12">{{ 'Help' | translate }}:</label>
                    <ckeditor :editor="editorHelp" v-model="currentQuestionHelp" v-on:input="runDebouncedChange" :config="editorHelpConfig"></ckeditor>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ls-space margin top-5 bottom-5" >
                    <hr/>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ls-space margin bottom-5">
                    <button class="btn btn-default pull-right" @click.prevent="triggerPreview">
                        {{previewActive ? "Hide Preview" : "Show Preview"}}
                    </button>
                </div>
                <div class="col-sm-12 ls-space margin top-5 bottom-5">
                    <div class="scope-preview" v-show="previewActive">
                        <PreviewFrame :id="'previewFrame'" :content="previewContent" :root-url="previewRootUrl" :loading="previewLoading" />
                    </div>
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
}
.scope-contains-ckeditor {
    min-height: 10rem;
}
</style>
