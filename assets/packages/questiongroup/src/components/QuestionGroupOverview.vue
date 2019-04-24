
<script>

import foreach from 'lodash/forEach';
import keys from 'lodash/keys';
import filter from 'lodash/filter';
import reduce from 'lodash/reduce';
import isEmpty from 'lodash/isEmpty';
import isObject from 'lodash/isObject';

import eventChild from '../mixins/eventChild.js';

export default {
    name: 'questiongroupoverview',
    mixin: [eventChild],
    data(){
        return {
            currentTab: '',
        };
    },
    computed: {
        cleanCurrentTitle(){
            return this.stripScripts(this.$store.state.currentQuestionGroupI10N[this.$store.state.activeLanguage].group_name);
        },
        cleancurrentQuestionGroupDescription(){
            return this.stripScripts(this.$store.state.currentQuestionGroupI10N[this.$store.state.activeLanguage].description);
        },
        currentQuestionGroupRandomgroup(){
            return this.$store.state.currentQuestionGroup.randomization_group;
            randomization_group
        },
        parsedRelevance(){
            return this.$store.state.currentQuestionGroup.grelevance;
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
            this.$emit('triggerEvent', { target: 'lsnextquestiongroupeditor', method: 'triggerEditQuestionGroup', content: {} });
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
            <ul class="list-group">
                <li class="list-group-item">
                    <div class="ls-flex-row wrap col-12">
                        <div class="col-12">{{'Title'|translate}}</div>
                        <div class="col-12 scoped-small-border" v-html="cleanCurrentTitle" />
                    </div>
                </li>
                <li class="list-group-item" v-show="!!cleancurrentQuestionGroupDescription">
                    <div class="ls-flex-row wrap col-12">
                        <div class="col-12">{{'Description'|translate}}</div>
                        <div class="col-12 scoped-small-border" v-html="cleancurrentQuestionGroupDescription" />
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="ls-flex-row wrap" :class="(parsedRelevance!=1 && parsedRelevance!='') ? 'col-6' : 'col-12'" v-show="!!currentQuestionGroupRandomgroup">
                        <div class="col-12">{{'Random Group'|translate}}</div>
                        <div class="col-12 scoped-small-border">
                            {{currentQuestionGroupRandomgroup}}
                        </div>
                    </div>
                    <div class="ls-flex-row wrap" :class="!!currentQuestionGroupRandomgroup ? 'col-6' : 'col-12'" v-show="(parsedRelevance!=1 && parsedRelevance!='')">
                        <div class="col-12">{{'Relevance'|translate}}</div>
                        <div class="col-12 scoped-small-border"  v-html="parsedRelevance" />
                    </div>
                </li>
            </ul>
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
