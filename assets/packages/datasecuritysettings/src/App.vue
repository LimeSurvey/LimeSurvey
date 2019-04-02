<script>
import Mousetrap from 'mousetrap';

import Editor from './components/Editor.vue';
import LanguageSelector from './subcomponents/_languageSelector.vue';

import runAjax from './mixins/runAjax.js';

export default {
    name: 'lsnexttexteditor',
    mixins: [runAjax],
    data() {
        return {
            event: null,
        }
    },
    components: {
        'editor' : Editor,
        'language-selector' : LanguageSelector,
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
        },
        selectLanguage(sLanguage) {
            this.$log.log('LANGUAGE CHANGED', sLanguage);
            this.$store.commit('setActiveLanguage', sLanguage);
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
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="questionCode">{{'Code' | translate }}</label>
                    <input type="text" class="form-control" id="questionCode" v-model="currentQuestionCode">
                </div>
            </div>
            <div class="row">
                <language-selector 
                    :elId="'questioneditor'" 
                    :aLanguages="$store.state.languages" 
                    :parentCurrentLanguage="$store.state.activeLanguage" 
                    @change="selectLanguage"
                />
            </div>
            <div class="row">
                <editor :label="'Description'" :editor-value="'dingDong'" />
            </div>
            <div class="row">
                <editor :label="'Welcome'" :editor-value="'dingDong'" />
            </div>
            <div class="row">
                <editor :label="'End message'" :editor-value="'dingDong'" />
            </div>
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
