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
        }
    },
    methods: {
        triggerEditQuestionGroup(){
            this.toggleLoading(true);
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
                    window.LS.notifyFader("Questiongroup could not be stored. Reloading page.", 'well-lg bg-danger text-center');
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
        this.$store.dispatch('loadQuestionGroup').then(
            (resolve) => {
                $('#questiongroupbarid').css({'display':''});
                if(this.$store.state.currentQuestionGroup.gid == null) {
                    $('#save-button').css({'display':'none'});
                    $('#questiongroupbar--savebuttons').css({'display':''});
                    $('#questiongroupbar--questiongroupbuttons').css({'display':'none'});
                    this.editQuestionGroup = true;
                } else {
                    $('#questiongroupbar--savebuttons').css({'display':'none'});
                    $('#questiongroupbar--questiongroupbuttons').css({'display':''});
                }
                this.toggleLoading(false);
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

        LS.EventBus.$on('saveButtonCalled', (payload) => {
            this.submitCurrentState(payload.id == '#save-and-close-button');
        });
        
        $('#save-button').on('click', (e)=>{
            this.submitCurrentState((this.$store.state.currentQuestionGroup.gid == null));
        });

        $('#save-and-close-button').on('click', (e)=>{
            this.submitCurrentState(true);
        });
        
        if(window.QuestionGroupEditData.startInEditView) {
            this.triggerEditQuestionGroup();
        }
    }
}
</script>

<template>
    <div class="container-center scoped-new-questioneditor">
        <template v-if="!loading">
            <div class="btn-group pull-right clear" v-if="allowSwitchEditing">
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
            <div class="pagetitle h3 scoped-unset-pointer-events">
                <template v-if="isCreateQuestionGroup">
                        {{'Create new question group'|translate}}
                </template>
                <template v-else>
                        {{'Question group'|translate}} <small>(ID: {{$store.state.currentQuestionGroup.gid}})</small>
                </template>
            </div>
            <div class="row" >
                <languageselector
                    :elId="'questiongroup-language-changer'"
                    :aLanguages="$store.state.languages"
                    :parentCurrentLanguage="$store.state.activeLanguage"
                    @change="selectLanguage"
                />
            </div>
            <div class="row scoped-contain-slider">
                <transition name="slide-fade-left">
                    <question-group-overview v-show="!(editQuestionGroup || isCreateQuestionGroup)" :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></question-group-overview>
                </transition>
                <transition name="slide-fade-left">
                    <question-group-editor v-show="(editQuestionGroup || isCreateQuestionGroup)" :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet"></question-group-editor>
                </transition>
            </div>
            <div class="row">
                <question-list :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet" :readonly="!(editQuestionGroup || isCreateQuestionGroup)"></question-list>
            </div>
        </template>
        <template v-if="loading">
            <loader-widget id="questiongroupEditLoader" />
        </template>
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
