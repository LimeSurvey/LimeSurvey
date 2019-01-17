<script>

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import debounce from 'lodash/debounce';
import PreviewFrame from './subcomponents/_previewFrame.vue';
import runAjax from '../mixins/runAjax.js';

export default {
    name: 'MainEditor',
    mixins: [runAjax],
    components: {PreviewFrame},
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
    },
    methods: {
        triggerPreview(){
            this.previewActive=!this.previewActive
            if(this.previewActive) {
                this.getQuestionPreview();
            }
        },
        changeTriggered: debounce( function(content,event) {
            this.$log.log('Debounced load triggered',{content,event});
            this.getQuestionPreview();
        }, 3000),
        getQuestionPreview(){
            this.previewLoading = true;
            this.$_load(
                this.previewRootUrl+'/sLanguage/'+this.$store.state.activeLanguage, 
                { 
                    'changedText': this.currentQuestionI10N,
                },
                'POST'
            ).then((result) => {
                this.previewContent = result.data;
                this.previewLoading = false;
            });
        },
        selectLanguage(sLanguage) {
            this.$store.commit('setActiveLanguage', sLanguage);
            this.editorQuestionData = this.$store.getters.currentQuestionI10NQuestion;
            this.editorHelpData = this.$store.getters.currentQuestionI10NHelp;
        }
    },
    mounted(){
        this.selectLanguage(this.$store.state.languages[0]);
        this.getQuestionPreview();
        this.currentQuestionCode = this.$store.state.currentQuestion.title;
    },
}
</script>

<template>
    <div class="col-sm-8 col-xs-12 ls-space padding all-5">
        <div class="container-center">
            <div class="form-group col-sm-12">
                <label for="questionCode">{{'Code' | translate }}</label>
                <input type="text" class="form-control" id="questionCode" v-model="currentQuestionCode">
            </div>
            <ul class="nav nav-tabs" id="editor-tabs-selectors" role="tablist">
                <li class="nav-item" v-for="(language, index) in $store.state.languages" :key="language">
                    <a 
                        class="nav-link" 
                        :class="index==0 ? ' active' : ''" 
                        :id="language+'-tab'" :href="'#'+language+'-tabpane'"
                        @click="selectLanguage(language)"
                    >
                        {{language | translate}}
                    </a>
                </li>
            </ul>
            <div class="tab-content scope-set-min-height" id="editor-tabs" >
                    <div class="tab-pane" 
                        role="tabpanel" 
                        v-for="language in $store.state.languages" 
                        :class="$store.state.activeLanguage == language ? ' active' : ''" 
                        :id="'#'+language+'-tabpane'" 
                        :key="language"
                    >
                    <div class="container-center">
                        <div class="row">
                            <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                                <label class="col-sm-12">{{ 'Question' | translate }}:</label>
                                <ckeditor :editor="editorQuestion" v-model="currentQuestionQuestion" v-on:input="changeTriggered" :config="editorQuestionConfig"></ckeditor>
                            </div>
                            <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                                <label class="col-sm-12">{{ 'Help' | translate }}:</label>
                                <ckeditor :editor="editorHelp" v-model="currentQuestionHelp" v-on:input="changeTriggered" :config="editorHelpConfig"></ckeditor>
                            </div>
                            <div class="col-sm-12 ls-space margin top-15 bottom-5">
                                <hr />
                            </div>
                            <div class="col-sm-12 ls-space margin top-5 bottom-5">
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
