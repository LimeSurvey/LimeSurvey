
<script>
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import foreach from 'lodash/foreach';
import keys from 'lodash/keys';
import filter from 'lodash/filter';
import reduce from 'lodash/reduce';
import isEmpty from 'lodash/isEmpty';
import isObject from 'lodash/isObject';

import eventChild from '../mixins/eventChild.js';

export default {
    name: 'questionoverview',
    mixin: [eventChild],
    data(){
        return {
            currentTab: '',
            editorDescription: ClassicEditor,
            editorDescriptionConfig: {},
        };
    },
    computed: {
        currentTitle: {
            get() { return this.$store.state.currentQuestionGroupI10N[this.$store.state.activeLanguage].group_name; },
            set(newValue) {
                this.$store.commit('setCurrentQuestionGroupI10NForCurrentLanguage', {setting: 'group_name', newValue});
            }
        },
        currentQuestionGroupDescription: {
            get() { return this.$store.state.currentQuestionGroupI10N[this.$store.state.activeLanguage].description; },
            set(newValue) {
                this.$store.commit('setCurrentQuestionGroupI10NForCurrentLanguage', {setting: 'description', newValue});
            }
        },
        currentQuestionGroupRandomgroup: {
            get(){ return this.$store.state.currentQuestionGroup.randomization_group; },
            set(newValue) {
                this.$store.commit('setCurrentQuestionGroupSetting', {setting: 'randomization_group', newValue});
            }
        },
        currentRelevance :{
            get(){ this.$store.state.currentQuestionGroup.grelevance; },
            set(newValue) {
                this.$store.commit('setCurrentQuestionGroupSetting', {setting: 'grelevance', newValue});
            }
        },
    },
    methods: {
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
        selectLanguage(sLanguage) {
            this.$log.log('LANGUAGE CHANGED', sLanguage);
            this.$store.commit('setActiveLanguage', sLanguage);
        },
        parseForLocalizedOption(value) {
            if(isObject(value) && value[this.$store.state.activeLanguage] != undefined) {
                return value[this.$store.state.activeLanguage];
            }
            return value;
        },
        toggleEditMode(){
            this.$emit('triggerEvent', { target: 'lsnextquestiongroupeditor', method: 'toggleOverview', content: {} });
        }
    },
    mounted(){
        this.toggleLoading(false);
    }
}
</script>

<template>
    <div class="col-sm-12 col-xs-12">
        <div class="panel panel-default" @dblclick="toggleEditMode">
            <div class="panel-heading">
            {{'Group overview'|translate}}
            </div>
            <div class="panel-body">
                <div class="col-xs-12 ls-space margin top-5 bottom-5">
                    <div class="col-12">{{'Title'|translate}}</div>
                    <input v-model="currentTitle" class="form-control" />
                </div>
                <div class="col-xs-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor">
                    <div class="col-12">{{'Description'|translate}}</div>
                    <ckeditor :editor="editorDescription" v-model="currentQuestionGroupDescription" :config="editorDescriptionConfig"></ckeditor>
                </div>
                <div class="col-sm-6 col-xs-12 ls-space margin top-5 bottom-5">
                    <div class="col-12">{{'Random Group'|translate}}</div>
                    <input v-model="currentQuestionGroupRandomgroup" class="form-control" />
                </div>
                <div class="col-sm-6 col-xs-12 ls-space margin top-5 bottom-5">
                    <div class="col-12">{{'Relevance'|translate}}</div>
                    <input v-model="currentRelevance" class="form-control" />
                </div>
            </div>
        </div>
    </div>
</template>

<style type="scss" scoped>
 .scoped-small-border{
     border: 1px solid rgba(184,184,184,0.8);
     padding: 1rem;
     border-radius: 4px;
 }
.scope-border-open-top {
    border-left: 1px solid #cfcfcf;
    border-right: 1px solid #cfcfcf;
    border-bottom: 1px solid #cfcfcf;
}
.scoped-fit-padding {
    padding-left: 15px;
    padding-right: 15px;
    padding-bottom: 5px;
}
</style>
