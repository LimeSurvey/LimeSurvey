
<template>
    <div class="ls-flex grow-3" id="QuestionEditor--test--questionoverviewcontainer">
        <transition name="slide-fade">
            <div v-if="!loading"  class="panel panel-default col-12" @dblclick="toggleEditMode">
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
                    <li class="list-group-item" v-show="!!cleanCurrentQuestionHelp">
                        <div class="ls-flex-row wrap col-12">
                            <div class="col-12">{{'Help'|translate}}</div>
                            <div class="col-12 scoped-small-border" v-html="cleanCurrentQuestionHelp" />
                        </div>
                    </li>
                    <li class="list-group-item" v-show="!!currentQuestionScript">
                        <div class="ls-flex-row wrap col-12">
                            <div class="col-12">{{'Script'|translate}}</div>
                            <div class="col-12 scoped-small-border">
                                {{currentQuestionScript}}
                            </div>
                            <p class="alert well">{{"__SCRIPTHELP"|translate}}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </transition>
        <transition name="slide-fade">
            <div class="row" v-if="loading">
                <loader-widget id="questionoverviewLoader" />
            </div>
        </transition>
    </div>
</template>

<script>

import foreach from 'lodash/forEach';
import keys from 'lodash/keys';
import filter from 'lodash/filter';
import reduce from 'lodash/reduce';
import isEmpty from 'lodash/isEmpty';
import isObject from 'lodash/isObject';

import eventChild from '../mixins/eventChild.js';

export default {
    name: 'questionoverview',
    mixin: [eventChild],
    props: {
        loading: {type: Boolean, default: false},
    },
    data(){
        return {
            currentTab: '',
        };
    },
    computed: {
        cleanCurrentQuestion(){
            return this.stripScripts(this.$store.state.currentQuestionI10N[this.$store.state.activeLanguage].question_expression);
        },
        cleanCurrentQuestionHelp(){
            return this.stripScripts(this.$store.state.currentQuestionI10N[this.$store.state.activeLanguage].help_expression);
        },
        currentQuestionScript(){
            return this.$store.state.currentQuestionI10N[this.$store.state.activeLanguage].script;
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
        parseForLocalizedOption(value) {
            if(isObject(value) && value[this.$store.state.activeLanguage] != undefined) {
                return value[this.$store.state.activeLanguage];
            }
            return value;
        },
        toggleEditMode(){
            this.$emit('triggerEvent', { target: 'lsnextquestioneditor', method: 'triggerEditQuestion', content: {} });
        }
    }
}
</script>

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
