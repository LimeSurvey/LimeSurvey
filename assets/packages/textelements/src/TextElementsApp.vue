<script>
import Mousetrap from 'mousetrap';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

import LanguageSelector from './components/subcomponents/_languageSelector.vue';
import runAjax from './mixins/runAjax.js';

export default {
    name: 'lsnexttexteditor',
    components: {
        'language-selector' : LanguageSelector,
    },
    mixins: [runAjax],
    data() {
        return {
            loading: true,
            event: null,
            descriptionEditorObject: ClassicEditor,
            welcomeEditorObject: ClassicEditor,
            endTextEditorObject: ClassicEditor,
        }
    },
    computed: {
        isNewSurvey() {
            return window.TextEditData.isNewSurvey;
        },
        currentSurveyTitle: {
            get() { return this.$store.state.surveyTitle[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setSurveyTitleForCurrentLanguage', newValue); },
        },
        currentWelcome: {
            get() { return this.$store.state.welcome[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setWelcomeForCurrentLanguage', newValue); },
        },
        currentDescription: {
            get() { return this.$store.state.description[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setDescriptionForCurrentLanguage', newValue); },
        },
        currentEndText: {
            get() { return this.$store.state.endText[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setEndTextForCurrentLanguage', newValue); },
        },
        currentEndUrl: {
            get() { return this.$store.state.endUrl[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setEndUrlForCurrentLanguage', newValue); },
        },
        currentEndUrlDescription: {
            get() { return this.$store.state.endUrlDescription[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setEndUrlDescriptionForCurrentLanguage', newValue); },
        },
        currentDateFormat: {
            get() { return this.$store.state.dateFormat[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setDateFormatForCurrentLanguage', newValue); },
        },
        currentDecimalDivider: {
            get() { return this.$store.state.decimalDivider[this.$store.state.activeLanguage]; },
            set(newValue) { this.$store.commit('setDecimalDividerForCurrentLanguage', newValue); },
        },
    },
    methods: {
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
        submitCurrentState(redirect = false) {
            this.toggleLoading();
            this.$store.dispatch('saveData').then(
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
                    window.LS.notifyFader("Texts could not be stored. Reloading page.", 'well-lg bg-danger text-center');
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
        this.$store.dispatch('getDateFormatOptions');
        this.$store.dispatch('getDataSet');
    },
    
    mounted() {
        $('#advancedTextEditor').on('jquery:trigger', this.jqueryTriggered);
        this.applyHotkeys();

        $('#surveytexts').on('submit', (e)=>{
            e.preventDefault();
        });

        if(!window.TextEditData.isNewSurvey) {
            $('#save-button').on('click', (e)=>{
                this.submitCurrentState();
            });
        }


        this.toggleLoading(false);
        
    }
}
</script>

<template>
    <div class="container-center scoped-new-texteditor">
        <template v-show="!loading">
            <div class="row" v-if="$store.state.languages.length > 1">
                <language-selector 
                    :elId="'texteditor'" 
                    :aLanguages="$store.state.languages" 
                    :parentCurrentLanguage="$store.state.activeLanguage" 
                    @change="selectLanguage"
                />
            </div>
            <div class="row">
                <div class="form-group col-md-4 col-sm-6">
                    <label for="surveyTitle">{{'Survey title' | translate }}</label>
                    <input type="text" class="form-control" name="surveyls_title" id="surveyTitle" v-model="currentSurveyTitle">
                </div>
                <div class="form-group col-md-4 col-sm-6">
                    <label for="dateFormat">{{'Date format' | translate }}</label>
                    <select class="form-control" id="dateFormat" name="dateformat" v-model="currentDateFormat">
                        <option 
                            v-for="(dateFormatOptionDescription, dateFormatOption) in $store.state.dateFormatOptions"
                            :key="dateFormatOption"
                            :value="dateFormatOption"
                        > {{dateFormatOptionDescription}} </option>
                    </select>
                </div>
                <div class="form-group col-md-4 col-sm-12">
                    <label for="">{{'Decimal mark' | translate }}</label>
                    <div class="fullystyled--radioButtons" role="group">
                        <div class="radioButtons--container">
                            <input type="radio" class="radio" id="decimalDivider-0" name="numberformat" :value="0" v-model="currentDecimalDivider">
                            <label for="decimalDivider-0"> {{"Dot " |translate}} (.) </label>
                        </div>
                        <div class="radioButtons--container">
                            <input type="radio" class="radio" id="decimalDivider-1" name="numberformat" :value="1" v-model="currentDecimalDivider">
                            <label for="decimalDivider-1"> {{"Comma " |translate}} (,) </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-4">
                    <label for="endUrl">{{'End url' | translate }}</label>
                    <input type="text" name="url" class="form-control" id="endUrl" v-model="currentEndUrl">
                </div>
                <div class="form-group col-sm-8">
                    <label for="endUrlDescription">{{'URL description (link text)' | translate }}</label>
                    <input type="text" name="urldescrip" class="form-control" id="endUrlDescription" v-model="currentEndUrlDescription">
                </div>
            </div>
            <div class="row scoped-editor-row">
                <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                    <label class="">{{ "Description" | translate }}:</label>
                    <ckeditor :editor="descriptionEditorObject" v-model="currentDescription" :config="{}"></ckeditor>
                    <input type="hidden" name="description" v-model="currentDescription" />
                </div>
            </div>
            <div class="row scoped-editor-row">
                <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                    <label class="">{{ "Welcome" | translate }}:</label>
                    <ckeditor :editor="welcomeEditorObject" v-model="currentWelcome" :config="{}"></ckeditor>
                    <input type="hidden" name="welcome" v-model="currentWelcome" />
                </div>
            </div>
            <div class="row scoped-editor-row">
                <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                    <label class="">{{ "End message" | translate }}:</label>
                    <ckeditor :editor="endTextEditorObject" v-model="currentEndText" :config="{}"></ckeditor>
                    <input type="hidden" name="endtext" v-model="currentEndText" />
                </div>
            </div>
        </template>
        <div class="loading-back-greyed" v-show="loading"></div>
    </div>
</template>

<style lang="scss" scoped>
.loading-back-greyed {
    background-color: rgba(200,200,200,0.4);
    width: 100%;
    min-height: 65vh;
    position: absolute;
    top:0;
    left:0;
}

.scoped-editor-row {
    &:before {
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        border-top: 1px solid #dedede;
        width: 95%;
        margin: 0.5rem auto;
        display: block
    }
}
</style>

