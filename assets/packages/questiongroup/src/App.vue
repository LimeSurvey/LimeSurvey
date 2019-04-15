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
        }
    },
    computed: {
        isCreateQuestionGroup(){
            return this.$store.state.currentQuestionGroup.gid == null;
        },
    },
    methods: {
        triggerEditQuestionGroup(){
            this.toggleLoading(true);
            if(this.editQuestionGroup) {
                $('#questionbarid').slideDown()
                $('#questiongroupbarid').slideUp();
            } else {
                $('#questionbarid').slideUp();
                $('#questiongroupbarid').slideDown()
            }
            this.editQuestionGroup = !this.editQuestionGroup;
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
        toggleOverview() {
            this.editQuestionGroup = !this.editQuestionGroup;
        },
        submitCurrentState(redirect = false) {
            this.toggleLoading();
            this.$store.dispatch('saveQuestionGroupData').then(
                (result) => {
                    this.toggleLoading();
                    if(redirect == true) {
                        window.location.href = result.data.redirect;
                    }

                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader(result.data.message, 'well-lg bg-primary text-center');
                    this.$log.log('OBJECT AFTER TRANSFER: ', result);
                },
                (reject) => {
                    this.toggleLoading();
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader("Question could not be stored. Reloading page.", 'well-lg bg-danger text-center');
                    //setTimeout(()=>{window.location.reload();}, 1500);
                }
            )
        },
        selectLanguage(sLanguage) {
            this.$log.log('LANGUAGE CHANGED', sLanguage);
            this.$store.commit('setActiveLanguage', sLanguage);
        },
    },
    created(){
        this.$store.dispatch('loadQuestionGroup');
        this.$store.dispatch('getQuestionsForGroup');
    },
    
    mounted() {
        $('#advancedQuestionEditor').on('jquery:trigger', this.jqueryTriggered);
        this.applyHotkeys();

        $('#frmeditgroup').on('submit', (e)=>{
            e.preventDefault();
        });

        $('#save-button').on('click', (e)=>{
            this.submitCurrentState();
        });

        $('#save-and-close-button').on('click', (e)=>{
            this.submitCurrentState(true);
        });

        this.toggleLoading(false);
        $('#questionbarid').css({'display': ''});
        $('#questiongroupbarid').css({'display':'none'});
    }
}
</script>

<template>
    <div class="container-center scoped-new-questioneditor">
        <button 
            v-if="!isCreateQuestionGroup" 
            @click.prevent.stop="triggerEditQuestionGroup" 
            class="pull-right clear btn "
            :class="editQuestionGroup ? 'btn-primary' : 'btn-default'"
        >
            {{editQuestionGroup ? 'Question overview' : 'Question editor'}}
        </button>
        <div class="pagetitle h3 scoped-unset-pointer-events">
            <template v-if="isCreateQuestionGroup">
                    {{'Create new question group'|translate}}
            </template>
            <template v-else>
                    {{'Question group'|translate}} <small>(ID: {{$store.state.currentQuestionGroup.gid}})</small>
            </template>
        </div>
        <template v-if="$store.getters.fullyLoaded">
            <div class="row">
                <languageselector
                    :elId="'questiongroup-language-changer'" 
                    :aLanguages="$store.state.languages" 
                    :parentCurrentLanguage="$store.state.activeLanguage" 
                    @change="selectLanguage"
                />
            </div>
            <div class="row">
                <question-group-overview v-show="(editQuestionGroup || isCreateQuestionGroup)" :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></question-group-overview>
                <question-group-editor v-show="!(editQuestionGroup || isCreateQuestionGroup)" :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></question-group-editor>
                <question-list :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet" :readonly="!(editQuestionGroup || isCreateQuestionGroup)"></question-list>
            </div>
        </template>
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

.slide-fade-enter-active {
  transition: all .3s ease;
}
.slide-fade-leave-active {
  transition: all .8s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}
.slide-fade-enter
/* .slide-fade-leave-active below version 2.1.8 */ {
  transform: translateX(-10px);
  opacity: 0;
}
.slide-fade-enter-to
/* .slide-fade-leave-active below version 2.1.8 */ {
  transform: translateX(0px);
  opacity: 1;
}
.slide-fade-leave-to
/* .slide-fade-leave-active below version 2.1.8 */ {
  transform: translateX(10px);
  opacity: 0;
}
</style>
