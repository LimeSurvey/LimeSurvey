<script>
import Mousetrap from 'mousetrap';

import QuestionOverview from './components/questionoverview.vue';
import MainEditor from './components/mainEditor.vue';
import GeneralSettings from './components/generalSettings.vue';
import AdvancedSettings from './components/advancedSettings.vue';

import runAjax from './mixins/runAjax.js';

export default {
    name: 'lsnextquestioneditor',
    mixins: [runAjax],
    components: {
        'questionoverview' : QuestionOverview,
        'maineditor' : MainEditor,
        'generalsettings' : GeneralSettings,
        'advancedsettings' : AdvancedSettings,
    },
    data() {
        return {
            event: null,
            editQuestion: false
        }
    },
    computed: {
        isCreateQuestion(){
            return this.$store.state.currentQuestion.qid == null;
        }
    },
    methods: {
        triggerEditQuestion(){
            this.toggleLoading(true);
            if(this.editQuestion) {
                $('#questionbarid').css('display', '');
                $('#questiongroupbarid').css('display','none');
            } else {
                $('#questionbarid').css('display', 'none');
                $('#questiongroupbarid').css('display','');
            }
            this.editQuestion = !this.editQuestion;
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
        eventSet() {
            this.event = null;
        },
        toggleOverview() {
            this.editQuestion = !this.editQuestion;
        },
        submitCurrentState(redirect = false) {
            this.toggleLoading();
            this.$store.dispatch('saveQuestionData').then(
                (result) => {
                    this.toggleLoading();
                    if(redirect == true) {
                        window.location.href = result.data.redirect;
                    }

                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader(result.data.message, 'well-lg bg-primary text-center');
                    this.$store.dispatch('updateObjects', result.data.newQuestionDetails)
                    this.event = { target: 'MainEditor', method: 'getQuestionPreview', content: {} };
                    this.$log.log('OBJECT AFTER TRANSFER: ', result);
                },
                (reject) => {
                    this.toggleLoading();
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader("Question could not be stored. Reloading page.", 'well-lg bg-danger text-center');
                    //setTimeout(()=>{window.location.reload();}, 1500);
                }
            )
        }

    },
    created(){
        this.$store.dispatch('loadQuestion');
        this.$store.dispatch('getQuestionTypes');
        this.$store.dispatch('getQuestionGeneralSettings');
        this.$store.dispatch('getQuestionAdvancedSettings');
    },
    
    mounted() {
        $('#advancedQuestionEditor').on('jquery:trigger', this.jqueryTriggered);
        this.applyHotkeys();

        $('#frmeditquestion').on('submit', (e)=>{
            e.preventDefault();
        });

        $('#save-button').on('click', (e)=>{
            this.submitCurrentState();
        });

        $('#save-and-close-button').on('click', (e)=>{
            this.submitCurrentState(true);
        });

        this.toggleLoading(false);
        $('#questionbarid').css('display', '');
        $('#questiongroupbarid').css('display','none');
    }
}
</script>

<template>
    <div class="container-center scoped-new-questioneditor">
        <button 
            v-if="!isCreateQuestion" 
            @click.prevent.stop="triggerEditQuestion" 
            class="pull-right clear btn "
            :class="editQuestion ? 'btn-primary' : 'btn-default'"
        >
            {{editQuestion ? 'Question overview' : 'Question editor'}}
        </button>
        <div class="pagetitle h3 scoped-unset-pointer-events">
            <template v-if="isCreateQuestion">
                    {{'Create new Question'|translate}}
            </template>
            <template v-else>
                    {{'Question'|translate}}: {{$store.state.currentQuestion.title}}&nbsp;&nbsp;<small>(ID: {{$store.state.currentQuestion.qid}})</small>
            </template>
        </div>
        <template v-if="$store.getters.fullyLoaded">
            <transition name="fade">
                <div class="row" v-if="editQuestion || isCreateQuestion">
                    <maineditor :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></maineditor>
                    <generalsettings :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></generalsettings>
                    <advancedsettings :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></advancedsettings>
                </div>
            </transition>
            <transition name="fade">
                <div class="row" v-if="!editQuestion && !isCreateQuestion">
                    <questionoverview :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></questionoverview>
                </div>
            </transition>
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
