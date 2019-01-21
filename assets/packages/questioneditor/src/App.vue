<script>
import MainEditor from './components/mainEditor.vue';
import GeneralSettings from './components/generalSettings.vue';
import AdvancedSettings from './components/advancedSettings.vue';

export default {
    name: 'lsnextquestioneditor',
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
        }

    },
    created(){
        this.$store.dispatch('loadQuestion');
        this.$store.dispatch('getQuestionTypes');
        this.$store.dispatch('getQuestionGeneralSettings');
    }
}
</script>

<template>
    <div class="container-center">
        <template v-if="$store.getters.fullyLoaded">
            <maineditor :event="event" v-on:eventSet="eventSet"></maineditor>
            <generalsettings :event="event" v-on:eventSet="eventSet"></generalsettings>
            <advancedsettings :event="event" v-on:eventSet="eventSet"></advancedsettings>
        </template>
    </div>
</template>

<style scoped>
.loading-back-greyed {
    background-color: rgba(200,200,200,0.4);
    width: 100%;
    height: 100%;
    min-height: 60vh;
}
</style>
