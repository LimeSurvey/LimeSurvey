
<script>

import keys from 'lodash/keys';
import filter from 'lodash/filter';

import LanguageSelector from './subcomponents/_languageSelector.vue';
import eventChild from '../mixins/eventChild.js';

export default {
    name: 'questionoverview',
    mixin: [eventChild],
    components: [LanguageSelector],
    data(){
        return {
            currentTab: '',
        };
    },
    computed: {
        tabs(){
            return filter(keys(this.$store.state.currentQuestionAdvancedSettings), (category) => category != 'debug');
        },
        currentAdvancedSettingsList() {
            return this.$store.state.currentQuestionAdvancedSettings[this.currentTab];
        },
        questionGroupWithId(){
            return `${this.$store.state.currentQuestionGroupInfo[this.$store.state.activeLanguage].group_name} (GID: ${this.$store.state.currentQuestionGroupInfo.gid})`;
        },
        cleanCurrentQuestion(){
            return this.stripScripts(this.$store.state.currentQuestionI10N[this.$store.state.activeLanguage].question);
        },
        cleanCurrentQuestionHelp(){
            return this.stripScripts(this.$store.state.currentQuestionI10N[this.$store.state.activeLanguage].help);
        },
        getNiceQuestionType(){
            return `${this.$store.state.currentQuestion.typeInformation.description} (${this.translate('Group')}: ${this.$store.state.currentQuestion.typeInformation.group})`;
        },
        getNiceMandatory(){
            let result = this.$store.state.currentQuestion.mandatory == 'Y' ? this.translate('Yes') : this.translate('No');
            result += `&nbsp;<i class='fa ${(this.$store.state.currentQuestion.mandatory == 'Y' ? "fa-check" : "fa-times")} fa-ls'></i>`;
            return result;
        },
        getNiceEncrypted(){
            let result = this.$store.state.currentQuestion.encrypted == 'Y' ? this.translate('Yes') : this.translate('No');
            result += `&nbsp;<i class='fa ${(this.$store.state.currentQuestion.encrypted == 'Y' ? "fa-check" : "fa-times")} fa-ls'></i>`;
            return result;
        },
        parsedRelevance(){
            return this.$store.state.currentQuestion.relevance;
        },
    },
    methods: {
         stripScripts(s) {
            const div = document.createElement('div');
            div.innerHTML = s;
            const scripts = div.getElementsByTagName('script');
            let i = scripts.length;
            while (i--) {
                scripts[i].parentNode.removeChild(scripts[i]);
            }
            return div.innerHTML;
        },
        selectLanguage(sLanguage) {
            this.$log.log('LANGUAGE CHANGED', sLanguage);
            this.$store.commit('setActiveLanguage', sLanguage);
        },
    },
    mounted(){
        this.currentTab = this.tabs[0];
        this.toggleLoading(false);
    }
}
</script>

<template>
    <div class="col-sm-12">
        <div class="container-center">
            <div class="row">
                <language-selector 
                    :elId="'questionpreview'" 
                    :aLanguages="$store.state.languages" 
                    :parentCurrentLanguage="$store.state.activeLanguage" 
                    @change="selectLanguage"
                />
            </div>
            <div class="row">
                <div class="col-sm-6 ls-space margin bottom-15">
                    <div class="scoped-small-border row">
                        <div class="col-sm-4">{{'Code'|translate}}:</div>
                        <div class="col-sm-8">{{this.$store.state.currentQuestion.title}}</div>
                    </div>
                </div>
                <div class="col-sm-6 ls-space margin bottom-15">
                    <div class="scoped-small-border row">
                        <div class="col-sm-4">{{'Question group'|translate}}:</div>
                        <div class="col-sm-8">{{questionGroupWithId}}</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                        {{'Text elements'|translate}}
                        </div>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <div class="ls-flex-row wrap col-12">
                                    <div class="col-12">{{'Question'|translate}}</div>
                                    <div class="col-12 scoped-small-border" v-html="cleanCurrentQuestion" />
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="ls-flex-row wrap col-12">
                                    <div class="col-12">{{'Help'|translate}}</div>
                                    <div class="col-12 scoped-small-border" v-html="cleanCurrentQuestionHelp" />
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                        {{'General settings'|translate}}
                        </div>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <div class="ls-flex-row col-12">
                                    <div class="col-4">Type</div>
                                    <div class="col-8"> {{getNiceQuestionType}} </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="ls-flex-row col-12">
                                    <div class="col-4">Mandatory</div>
                                    <div class="col-8" v-html="getNiceMandatory"/>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="ls-flex-row col-12">
                                    <div class="col-4">Encrypted</div>
                                    <div class="col-8" v-html="getNiceEncrypted"/>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="ls-flex-row col-12">
                                    <div class="col-4">Relevance equation</div>
                                    <div class="col-8" v-html="parsedRelevance"/>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                        {{'Advanced settings'|translate}}
                        </div>
                        <div class="row ls-space margin top-5">
                            <div class="col-sm-12">
                                <ul class="nav nav-tabs scoped-tablist-advanced-settings" role="tablist">
                                    <li 
                                        v-for="advancedSettingCategory in tabs"
                                        :key="'tablist-'+advancedSettingCategory"
                                        :class="currentTab==advancedSettingCategory ? 'active' : ''" 
                                    >
                                        <a href="#" @click.prevent.stop="currentTab=advancedSettingCategory" >{{advancedSettingCategory}}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row ls-space scoped-fit-padding">
                            <div class="col-sm-12 scope-border-open-top ls-space padding all-5">
                                <ul class="list-group col-sm-12 ">
                                    <li class="list-group-item" v-for="advancedSetting in currentAdvancedSettingsList" :key="advancedSetting.name">
                                        <div class="ls-flex-row col-12">
                                            <div class="col-4">{{advancedSetting.title}}</div>
                                            <div class="col-8">{{advancedSetting.formElementValue}}</div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
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
