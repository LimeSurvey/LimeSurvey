<script>
import Mousetrap from 'mousetrap';

import MainEditor from './components/mainEditor.vue';
import GeneralSettings from './components/generalSettings.vue';
import AdvancedSettings from './components/advancedSettings.vue';

import runAjax from './mixins/runAjax.js';

export default {
    name: 'lsnextquestioneditor',
    mixins: [runAjax],
    data() {
        return {
            event: null,
        }
    },
    components: {
        'maineditor' : MainEditor,
        'generalsettings' : GeneralSettings,
        'advancedsettings' : AdvancedSettings,
    },
    methods: {
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
        eventSet() {
            this.event = null;
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
                    setTimeout(()=>{window.location.reload();}, 1500);
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
    }
}
</script>

<template>
    <div class="container-center scoped-new-questioneditor">
        <template v-if="$store.getters.fullyLoaded">
            <maineditor :event="event" v-on:eventSet="eventSet"></maineditor>
            <generalsettings :event="event" v-on:eventSet="eventSet"></generalsettings>
            <advancedsettings :event="event" v-on:eventSet="eventSet"></advancedsettings>
        </template>
        <modals-container @modalEvent="setModalEvent"/>
    </div>
</template>

<style scoped>
.scoped-new-questioneditor {
    min-height: 75vh;
}
.loading-back-greyed {
    background-color: rgba(200,200,200,0.4);
    width: 100%;
    height: 100%;
    min-height: 60vh;
}
</style>
