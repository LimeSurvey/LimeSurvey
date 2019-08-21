<script>
import Mousetrap from 'mousetrap';
import filter from 'lodash/filter';

import QuestionOverview from './components/questionoverview.vue';
import MainEditor from './components/mainEditor.vue';
import GeneralSettings from './components/generalSettings.vue';
import AdvancedSettings from './components/advancedSettings.vue';
import LanguageSelector from './helperComponents/LanguageSelector.vue';

import runAjax from './mixins/runAjax.js';
import eventRoot from './mixins/eventRoot.js';

export default {
    name: 'lsnextquestioneditor',
    mixins: [runAjax,eventRoot],
    components: {
        'questionoverview' : QuestionOverview,
        'maineditor' : MainEditor,
        'generalsettings' : GeneralSettings,
        'advancedsettings' : AdvancedSettings,
        'languageselector' : LanguageSelector,
    },
    data() {
        return {
            editQuestion: false,
            questionEditButton: window.questionEditButton,
            loading: true,
        }
    },
    computed: {
        showAlerts() {
            return this.$store.state.alerts.length > 0;
        },
        isCreateQuestion(){
            return this.$store.state.currentQuestion.qid == null;
        },
        questionGroupWithId(){
            return `${this.$store.state.currentQuestionGroupInfo[this.$store.state.activeLanguage].group_name} (GID: ${this.$store.state.currentQuestionGroupInfo.gid})`;
        },
        currentQuestionCode: {
            get() {return this.$store.state.currentQuestion.title;},
            set(newValue) {
                this.$store.commit('updateCurrentQuestionTitle', newValue);
            }
        },
        allowSwitchEditing(){
            return !this.isCreateQuestion && this.$store.state.currentQuestionPermissions.update;
        },
        currentAlerts: {
            get() {return this.$store.state.alerts;},
            set(tmpAlerts) { this.$store.commit('setAlerts', tmpAlerts); }
        },
        storedEvent() {
            return this.$store.state.storedEvent;
        }
    },
    watcher: {
        storedEvent(newValue) {
            if(newValue !== null) {
                this.event = newValue;
            }
            this.$store.commit('setStoredEvent', null);
        }
    },
    methods: {
        triggerEditQuestion(force = null){
            if(force === null) {
                this.editQuestion = !this.editQuestion;
            } else {
                this.editQuestion = force;
            }
            LS.EventBus.$emit('doFadeEvent', this.editQuestion);
        },
        toggleLoading(force=null){
            if(force===null) {
                this.loading = !this.loading;
                return;    
            }
            this.loading = force;
        },
        setEditQuestion(){
            if(!this.editQuestion) {
                this.editQuestion = true;
                LS.EventBus.$emit('doFadeEvent', true);
            }
        },
        applyHotkeys() {
            Mousetrap.bind('ctrl+right', this.chooseNextLanguage);
            Mousetrap.bind('ctrl+left', this.choosePreviousLanguage);
            Mousetrap.bind('ctrl+s', this.submitCurrentState);
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
        eventSet(eventRoot=false) {
            this.event = null;
        },
        submitCurrentState(redirect = false) {
            this.$store.dispatch('saveQuestionData').then(
                (result) => {
                    if(result === false) {
                        return;
                    }

                    if(redirect == true) {
                        window.location.href = result.data.redirect || window.location.href;
                    }

                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader(result.data.message, 'well-lg bg-primary text-center');
                    this.$store.dispatch('updateObjects', result.data.newQuestionDetails)
                    this.event = { target: 'MainEditor', method: 'getQuestionPreview', content: {} };
                    this.$log.log('OBJECT AFTER TRANSFER: ', result);
                },
                (reject) => {
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader("Question could not be stored. Reloading page.", 'well-lg bg-danger text-center');
                    setTimeout(()=>{window.location.reload();}, 1500);
                }
            )
        },
        questionTypeChangeTriggered(newValue) {
            this.$log.log('CHANGE OF TYPE', newValue);
            this.currentQuestionType = newValue;
            let tempQuestionObject = this.$store.state.currentQuestion;
            tempQuestionObject.type = newValue;
            this.$store.commit('setCurrentQuestion', tempQuestionObject);
            this.event = { target: 'GeneralSettings', method: 'toggleLoading', content: true, chain: 'AdvancedSettings' };
            Promise.all([
                this.$store.dispatch('getQuestionGeneralSettings'),
                this.$store.dispatch('getQuestionAdvancedSettings')
            ]).finally(()=>{
                this.event = { target: 'GeneralSettings', method: 'toggleLoading', content: false, chain: 'AdvancedSettings' };
            });
        },
        selectLanguage(sLanguage) {
            this.$log.log('LANGUAGE CHANGED', sLanguage);
            this.$store.commit('setActiveLanguage', sLanguage);
        },
    },
    created(){
        Promise.all([
            this.$store.dispatch('loadQuestion'),
            this.$store.dispatch('getQuestionTypes')
        ]).then(()=>{
            this.loading = false;
        })
    },

    mounted() {
        $('#advancedQuestionEditor').on('jquery:trigger', this.jqueryTriggered);
        this.applyHotkeys();

        $('#frmeditquestion').on('submit', (e)=>{
            e.preventDefault();
        });

        LS.EventBus.$on('saveButtonCalled', (payload) => {
            this.submitCurrentState(payload.id == '#save-and-close-button');
        });

        $('#save-button').on('click', (e)=>{
            e.preventDefault();
            this.submitCurrentState();
        });

        $('#save-and-close-button').on('click', (e)=>{
            e.preventDefault();
            this.submitCurrentState(true);
        });

        if(this.isCreateQuestion || window.QuestionEditData.startInEditView) {
           this.triggerEditQuestion(true);
        }
    }
}
</script>

<template>
    <div class="container-center scoped-new-questioneditor">
        <transition name="slide-fade-left">
            <div class="ls-flex ls-flex-row" v-show="showAlerts">
                <div
                    v-for="alert in currentAlerts"
                    :key="alert.key"
                    class="col-xs-12 alert"
                    :class="alert.class"
                    v-on:load="alert.shown=true"
                >
                    <button
                        type="button"
                        class="close"
                        @click="$store.commit('removeAlert', alert.key)"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{alert.message}}
                </div>
            </div>
        </transition>
        <div class="btn-group pull-right clear" v-if="allowSwitchEditing">
            <button
                id="questionOverviewButton"
                @click.prevent="triggerEditQuestion(false)"
                :class="editQuestion ? 'btn-default' : 'btn-primary'"
                class="btn "
            >
                {{'Question overview'| translate}}
            </button>
            <button
                id="questionEditorButton"
                @click.prevent="triggerEditQuestion(true)"
                :class="editQuestion ? 'btn-primary' : 'btn-default'"
                class="btn "
            >
                {{'Question editor'| translate}}
            </button>
        </div>
        <div class="pagetitle h3 scoped-unset-pointer-events">
            <template v-if="isCreateQuestion">
                    {{'Create new Question'|translate}}
            </template>
            <template v-else>
                    {{'Question'|translate}}: {{$store.state.currentQuestion.title}}&nbsp;&nbsp;<small>(ID: {{$store.state.currentQuestion.qid}})</small>
            </template>
        </div>
        <template v-if="!loading">
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="questionCode">{{'Code' | translate }}</label>
                    <input
                        type="text"
                        class="form-control"
                        id="questionCode"
                        :readonly="!(editQuestion || isCreateQuestion)"
                        v-model="currentQuestionCode"
                        @dblclick="setEditQuestion"
                    />
                </div>
                <div class="form-group col-sm-6 contains-question-selector">
                    <label for="questionCode">{{'Question type' | translate }}</label>
                    <div v-if="(editQuestion || isCreateQuestion) && $store.getters.surveyObject.active !='Y'"  v-html="questionEditButton" />
                    <input v-else type="text" class="form-control" id="questionTypeVisual" :readonly="true" :value="$store.state.currentQuestion.typeInformation.description+' ('+$store.state.currentQuestion.type+')'"/>
                    <input v-if="$store.getters.surveyObject.active !='Y'" type="hidden" id="question_type" name="type" @change="questionTypeChangeTriggered" :value="$store.state.currentQuestion.type" />
                </div>
            </div>
            <div class="row">
                <languageselector
                    :elId="'question-language-changer'"
                    :aLanguages="$store.state.languages"
                    :parentCurrentLanguage="$store.state.activeLanguage"
                    @change="selectLanguage"
                />
            </div>
            <div class="row">
                <transition name="slide-fade-left">
                    <maineditor :loading="loading" v-show="(editQuestion || isCreateQuestion)" :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></maineditor>
                </transition>
                <transition name="slide-fade-left">
                    <questionoverview :loading="loading" v-show="!(editQuestion || isCreateQuestion)" :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></questionoverview>
                </transition>
                <generalsettings :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet" :readonly="!(editQuestion || isCreateQuestion)"></generalsettings>
                <advancedsettings :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet" :readonly="!(editQuestion || isCreateQuestion)"></advancedsettings>
            </div>
        </template>
        <template v-if="loading">
            <loader-widget id="mainViewLoader" />
        </template>
        <modals-container @modalEvent="setModalEvent"/>
    </div>
</template>

<style scoped>
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

.scoped-small-border{
     border: 1px solid rgba(184,184,184,0.8);
     padding: 0.6rem 1rem;
     border-radius: 4px;
 }

</style>
