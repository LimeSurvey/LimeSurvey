<script>
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
        'advancedsettings' : AdvancedSettings
    },
    mounted() {
        this.toggleLoading(false);
        $('#advancedQuestionEditor').on('jquery:trigger', this.jqueryTriggered);
    },
    methods: {
        jqueryTriggered(event, data){
            //this.$log.log('data', data);
            this.event = JSON.parse(data.emitter);
        },
        eventSet() {
            this.event = null;
        },
        submitCurrentState() {
            this.toggleLoading();
            let transferObject = {
                question: this.$store.state.currentQuestion,
                scaledSubquestions: this.$store.state.currentQuestionSubquestions,
                scaledAnswerOptions: this.$store.state.currentQuestionAnswerOptions,
                questionSettings: this.$store.state.currentQuestionSettings,
                questionI10N: this.$store.state.currentQuestionI10N,
                questionAttributes: this.$store.state.currentQuestionAttributes,
                generalSettings: this.$store.state.currentQuestionGeneralSettings,
                advancedSettings: this.$store.state.currentQuestionAdvancedSettings,
            };
            this.$log.log('OBJECT TO BE TRANSFERRED: ', {'questionData': transferObject});
            this.$_post(window.QuestionEditData.connectorBaseUrl+'/saveQuestionData', {'questionData': transferObject}).then((result) => {
                this.toggleLoading();
                this.$log.log('OBJECT AFTER TRANSFER: ', result);
            })
        }

    },
    created(){
        this.$store.dispatch('loadQuestion');
        this.$store.dispatch('getQuestionTypes');
        this.$store.dispatch('getQuestionGeneralSettings');
        this.$store.dispatch('getQuestionAdvancedSettings');
    }
}
</script>

<template>
    <div class="container-center scoped-new-questioneditor">
        <input type="submit" class="hidden" name="triggerSubmitQuestionEditor" id="triggerSubmitQuestionEditor" @click.prevent="submitCurrentState" />
        <template v-if="$store.getters.fullyLoaded">
            <maineditor :event="event" v-on:eventSet="eventSet"></maineditor>
            <generalsettings :event="event" v-on:eventSet="eventSet"></generalsettings>
            <advancedsettings :event="event" v-on:eventSet="eventSet"></advancedsettings>
        </template>
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
