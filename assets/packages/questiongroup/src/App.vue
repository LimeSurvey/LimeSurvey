<script>
import Mousetrap from 'mousetrap';

import QuestionGroupOverview from './components/QuestionGroupOverview.vue';
import QuestionGroupEditor from './components/QuestionGroupEditor.vue';
import QuestionList from './components/QuestionList.vue';
import LanguageSelector from './helperComponents/LanguageSelector.vue';

import runAjax from './mixins/runAjax.js';
import eventRoot from './mixins/eventRoot.js';

export default {
    name: 'lsnextquestiongroupeditor',
    mixins: [runAjax,eventRoot],
    components: {
        QuestionGroupOverview,
        QuestionGroupEditor,
        QuestionList,
        'languageselector' : LanguageSelector
    },
    data() {
        return {
            editQuestionGroup: false,
            questionEditButton: window.questionEditButton,
            loading: true
        }
    },
    computed: {
        isCreateQuestionGroup(){
            return this.$store.state.currentQuestionGroup.gid == null;
        },
        allowSwitchEditing(){
            return !this.isCreateQuestionGroup && this.$store.state.permissions.update
        },
        getLanguages() {
            return this.$store.state.languages;
        },
        languagesCount() {
            let languages = this.getLanguages;
            let count = 0;
            for (let language in languages) {
                count += 1;
            }
            return count;
        },
        containsMultipleLanguages() {
            let ownsMultiple = (this.languagesCount > 1);
            return ownsMultiple;
        },
    },
    methods: {
        triggerEditQuestionGroup(){
            this.editQuestionGroup = !this.editQuestionGroup;
            LS.EventBus.$emit('doFadeEvent', this.editQuestionGroup);
        },
        applyHotkeys() {
            Mousetrap.bind('ctrl+right', this.chooseNextLanguage);
            Mousetrap.bind('ctrl+left', this.choosePreviousLanguage);
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
        jqueryTriggered(event, data){
            //this.$log.log('data', data);
            this.event = JSON.parse(data.emitter);
        },
        setModalEvent(payload) {
            this.$log.log('New event set', payload);
            this.event = payload;
        },
        triggerEvent(payload) {
            this.$log.log('New event set', payload);
            this.event = payload;
        },
        eventSet() {
            this.event = null;
        },
        submitCurrentState(redirect = false, redirectUrl = false, scenario = false) {
            this.loading = true;
            this.$store.dispatch('saveQuestionGroupData', scenario).then(
                (result) => {
                    window.LS.notifyFader(result.data.message, 'well-lg bg-primary text-center');
                    this.$log.log('OBJECT AFTER TRANSFER: ', result);
                    if(redirect == true || redirectUrl !== false ) {
                            window.location.href = redirectUrl || result.data.redirect;
                            return;
                    };
                    window.history.pushState({}, result.data.questionGroupId, result.data.redirect);
                    LS.EventBus.$emit('updateSideBar', {updateQuestions:true});
                    this.$store.dispatch('reloadQuestionGroup', result.data.questionGroupId).then()
                    .catch(
                         (error) => {
                             this.$log.error(error);
                         }
                    ).finally((result) => {
                        this.loading = false;
                        LS.EventBus.$emit('loadingFinished');
                    });
                },
                (reject) => {
                    this.loading = false;
                    LS.EventBus.$emit('loadingFinished');
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader("Question group could not be stored. Reloading page.", 'well-lg bg-danger text-center');
                    setTimeout(()=>{window.location.reload();}, 1500);
                }
            )
        },
        selectLanguage(sLanguage) {
            this.$log.log('LANGUAGE CHANGED', sLanguage);
            this.$store.commit('setActiveLanguage', sLanguage);
        },
    },
    created(){
        this.$store.dispatch('loadQuestionGroup').then(
            (resolve) => {
                if(this.$store.state.currentQuestionGroup.gid == null) {
                    this.editQuestionGroup = true;
                    LS.EventBus.$emit('doFadeEvent', true);
                }
                this.loading = false;
            },
            (reject) => {
                this.$log.error("Question group loading failed");
                this.loading = false;
            }

        );
        this.$store.dispatch('getQuestionsForGroup');
    },

    mounted() {
        $('#advancedQuestionEditor').on('jquery:trigger', this.jqueryTriggered);
        this.applyHotkeys();

        $('#frmeditgroup').on('submit', (e)=>{
            e.preventDefault();
        });

        LS.EventBus.$off('componentFormSubmit');
        LS.EventBus.$on('componentFormSubmit', (payload) => {
            let redirect = (payload.id == 'save-and-close-button' ||
                            payload.id == 'save-and-new-question-button' ||
                            payload.id == 'save-and-new-button');
            this.submitCurrentState(
                redirect, 
                payload.url != '#' ? payload.url : false, 
                payload.scenario || ''
            );
        });
        
        if(window.QuestionGroupEditData.startInEditView) {
            this.triggerEditQuestionGroup();
        }
    }
}
</script>

<template>
    <div class="container-center scoped-new-questioneditor">
        <transition-group name="fade">
            <template v-if="!loading">
                <div class="btn-group pull-right clear" v-if="allowSwitchEditing" key="switch-block">
                    <button
                        @click.prevent.stop="triggerEditQuestionGroup"
                        :class="editQuestionGroup ? 'btn-default' : 'btn-primary'"
                        class="btn "
                    >
                        {{'Question group overview'| translate}}
                    </button>
                    <button
                        @click.prevent.stop="triggerEditQuestionGroup"
                        :class="editQuestionGroup ? 'btn-primary' : 'btn-default'"
                        class="btn "
                    >
                        {{'Question group editor'| translate}}
                    </button>
                </div>
                <div class="pagetitle h3 scoped-unset-pointer-events" key="pagetitle-block">
                    <template v-if="isCreateQuestionGroup">
                            <x-test id="action::addQuestionGroup"></x-test>
                            {{'Create Question group'|translate}}
                    </template>
                    <template v-else>
                            {{'Question group'|translate}} <small>(ID: {{$store.state.currentQuestionGroup.gid}})</small>
                    </template>
                </div>
                <div class="row" key="languageselector-block" v-if="this.containsMultipleLanguages">
                    <languageselector
                        :elId="'questiongroup-language-changer'"
                        :aLanguages="$store.state.languages"
                        :parentCurrentLanguage="$store.state.activeLanguage"
                        @change="selectLanguage"
                    />
                </div>
                <div class="row scoped-contain-slider" key="editorcontent-block">
                    <transition name="slide-fade-left">
                        <question-group-overview v-show="!(editQuestionGroup || isCreateQuestionGroup)" :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></question-group-overview>
                    </transition>
                    <transition name="slide-fade-left">
                        <question-group-editor v-show="(editQuestionGroup || isCreateQuestionGroup)" :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></question-group-editor>
                    </transition>
                </div>
                <div class="row" key="questionlist-block">
                    <question-list :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet" :readonly="!(editQuestionGroup || isCreateQuestionGroup)"></question-list>
                </div>
            </template>
        </transition-group>
        <transition name="fade">
            <loader-widget id="questiongroupEditLoader" v-if="loading" />
        </transition>
    </div>
</template>

<style lang="scss" scoped>
.scoped-unset-pointer-events {
    pointer-events: none;
}

.scoped-new-questioneditor {
    min-height: 75vh;
}
.loading-back-greyed {
    background-color: rgba(200,200,200,0.4);
    width: 100%;
    height: 100%;
    min-height: 60vh;
}
.scoped-contain-slider {
    min-height: 200px;
    position:relative;
}
.scoped-small-border{
     border: 1px solid rgba(184,184,184,0.8);
     padding: 0.6rem 1rem;
     border-radius: 4px;
 }

</style>
