<script>

import keys from 'lodash/keys';
import foreach from 'lodash/forEach';
import reduce from 'lodash/reduce';
import filter from 'lodash/filter';
import isEmpty from 'lodash/isEmpty';
import isObject from 'lodash/isObject';

import SettingsTab from './subcomponents/_settingstab.vue';
import Subquestions from './subcomponents/_subquestions.vue';
import Answeroptions from './subcomponents/_answeroptions.vue';

import eventChild from '../mixins/eventChild.js';

export default {
    name: 'AdvancedSettings',
    mixins: [eventChild],
    components: {
        "settings-tab" : SettingsTab,
        "subquestions" : Subquestions,
        "Answeroptions" : Answeroptions,
    },
    props: {
        readonly : {type: Boolean, default: false}
    },
    data() {
        return {
            aComponentArray : [
                'switch',
                'text',
                'integer',
                'select',
                'textinput',
                'buttongroup',
                'textarea'
            ],
            currentTabComponent: 'settings-tab',
            loading: true
        };
    },
    computed: {
        tabs(){
            if(this.readonly == false) {
                return filter(keys(this.$store.state.currentQuestionAdvancedSettings), (category) => category != 'debug');
            }

            let tabsWithContent = [];
            foreach(this.$store.state.currentQuestionAdvancedSettings, (categoryContents, categoryName) => {
                if(reduce(categoryContents, (carry, settingOption) => {
                    return carry = carry || !isEmpty(this.parseForLocalizedOption(settingOption.formElementValue));
                }, false) && categoryName != 'debug') {
                    tabsWithContent.push(categoryName);
                }
            });
            return tabsWithContent;
            
        },
        showSubquestionEdit(){
            return this.$store.state.currentQuestion.typeInformation.subquestions >= 1;
        },
        showAnswerOptionEdit(){
            return this.$store.state.currentQuestion.typeInformation.answerscales >= 1;
        },
    },
    methods: {
        selectCurrentTab(tabComponent, categoryName='') {
            this.currentTabComponent = tabComponent;
            if(tabComponent === 'settings-tab') {
                this.$store.commit('setQuestionAdvancedSettingsCategory',categoryName);
            }
        },
        parseForLocalizedOption(value) {
            if(isObject(value) && value[this.$store.state.activeLanguage] != undefined) {
                return value[this.$store.state.activeLanguage];
            }
            return value;
        },
        toggleLoading(force=null){
            if(force===null) {
                this.loading = !this.loading;
                return;    
            }
            this.loading = force;
        }
    },
    created(){
        this.$store.dispatch('getQuestionAdvancedSettings').then(()=>{
            this.loading = false;
            this.selectCurrentTab('settings-tab', this.tabs[0]);
        });
    }
}
</script>

<template>
    <div class="col-xs-12 scope-apply-base-style scope-min-height">
        <transition name="slide-fade">
            <div class="container-fluid" v-if="!loading">
                <div class="row scoped-tablist-container">
                    <template v-if="showSubquestionEdit || showAnswerOptionEdit">
                        <ul class="nav nav-tabs scoped-tablist-subquestionandanswers" role="tablist">
                            <li 
                                v-if="showSubquestionEdit"
                                :class="currentTabComponent == 'subquestions' ? 'active' : ''"
                            >
                                <a href="#" @click.prevent.stop="selectCurrentTab('subquestions')" >{{"subquestions" | translate }}</a>
                            </li>
                            <li 
                                v-if="showAnswerOptionEdit"
                                :class="currentTabComponent == 'answeroptions' ? 'active' : ''"
                            >
                                <a href="#" @click.prevent.stop="selectCurrentTab('answeroptions')" >{{"answeroptions" | translate }}</a>
                            </li>
                        </ul>
                        <span class="scope-divider">|</span>
                    </template>
                    <!-- Advanced settings tabs -->
                    <ul class="nav nav-tabs scoped-tablist-advanced-settings" role="tablist">
                        <li 
                            v-for="advancedSettingCategory in tabs"
                            :key="'tablist-'+advancedSettingCategory"
                            :class="$store.state.questionAdvancedSettingsCategory == advancedSettingCategory && currentTabComponent == 'settings-tab' ? 'active' : ''"
                        >
                            <a href="#" @click.prevent.stop="selectCurrentTab('settings-tab', advancedSettingCategory)" >{{advancedSettingCategory}}</a>
                        </li>
                    </ul>
                </div>
                <div class="row scope-border-open-top">
                    <component 
                    v-bind:is="currentTabComponent" 
                    :event="event"
                    v-on:eventSet="eventSet"
                    v-on:triggerEvent="triggerEvent"
                    :readonly="readonly"
                    />
                </div>    
            </div>
        </transition>
        <transition name="slide-fade">
            <div class="container-fluid" v-if="loading" >
                <div class="row" >
                    <loader-widget id="advanced-panel-loader" />
                </div>
            </div>
        </transition>
    </div>
</template>

<style lang="scss" scoped>
.scope-divider{
    display: block;
    width: 2em;
    height:42px;
    line-height:42px;
    font-size: 38px;
    text-align: center;
}
.scoped-tablist-container {
    display: flex;
    width: 100%;
    flex-wrap: nowrap;
    align-content: flex-start;
}
.scoped-tablist-subquestionandanswers {
    flex-grow: 1;
    flex-shrink: 3;
    display: flex;
    width: 100%;
    flex-wrap: nowrap;
}
.scoped-tablist-advanced-settings {
    flex-grow: 3;
    flex-shrink: 1;
    display: flex;
    width: 100%;
    flex-wrap: nowrap;
}
.scoped-tablist-advanced-settings >li,
.scoped-tablist-subquestionandanswers >li {
    display: block;
    width: 100%;
    float: none;
    text-align: center;
}

.scope-min-height {
    min-height: 20vh;
}
.scope-apply-base-style {
    border-top: 1px solid #cfcfcf;
    margin-top: 1rem;
    padding-top: 1rem;
}
.scope-border-open-top {
    border-left: 1px solid #cfcfcf;
    border-right: 1px solid #cfcfcf;
    border-bottom: 1px solid #cfcfcf;
}
</style>