<script>
import Mousetrap from 'mousetrap';

import ClassicEditor from '../../meta/LsCkeditor/src/LsCkEditorClassic.js';
import LanguageSelector from './components/subcomponents/_languageSelector';
import Aceeditor from './helperComponents/AceEditor';

import runAjax from './mixins/runAjax.js';

export default {
    name: 'lsnexttexteditor',
    components: {
        'language-selector' : LanguageSelector,
        Aceeditor
    },
    mixins: [runAjax],
    props: {
        'languagelist': {
            'type': String,
            'default': () => { return '{}'; }
        },
        'languagename': {
            'type': String,
            'default': 'No language found'
        },
        'defaultlanguage': {
            'type': String,
            'default': 'en'
        }
    },
    data() {
        return {
            loading: true,
            event: null,
            descriptionEditorObject: ClassicEditor,
            descriptionEditorConfig: {
                'lsExtension:fieldtype': 'survey-desc', 
                'lsExtension:ajaxOptions': this.$store.getters.surveyid != null 
                    ? {surveyid: this.$store.getters.surveyid} 
                    : {},
                'lsExtension:currentFolder':  this.$store.getters.surveyid != null 
                    ? 'upload/surveys/'+this.$store.getters.surveyid+'/' 
                    : 'upload/global/',
            },
            welcomeEditorObject: ClassicEditor,
            welcomeEditorConfig: {
                'lsExtension:fieldtype': 'survey-welc', 
                'lsExtension:ajaxOptions': this.$store.getters.surveyid != null 
                    ? {surveyid: this.$store.getters.surveyid} 
                    : {},
                'lsExtension:currentFolder':  this.$store.getters.surveyid != null 
                    ? 'upload/surveys/'+this.$store.getters.surveyid+'/' 
                    : 'upload/global/',
            },
            endTextEditorObject: ClassicEditor,
            endTextEditorConfig: {
                'lsExtension:fieldtype': 'survey-endtext', 
                'lsExtension:ajaxOptions': this.$store.getters.surveyid != null 
                    ? {surveyid: this.$store.getters.surveyid} 
                    : {},
                'lsExtension:currentFolder':  this.$store.getters.surveyid != null 
                    ? 'upload/surveys/'+this.$store.getters.surveyid+'/' 
                    : 'upload/global/',
            },
            descriptionSource: false,
            welcomeSource: false,
            endTextSource: false,
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
        languageChangerEnabled() {
            return LS.ld.size(this.$store.state.languages) > 1;
        },
        languageList() {
            return JSON.parse(this.languagelist);
        },
        languageName() {
            return this.languagename;
        }
    },
    methods: {
        stopLoadingIcon() {
            $('#create-import-copy-survey').trigger('lsStopLoading');
        },
        CKErrorManagement(error) {
            this.$log.trace(error);
        },
        CKEventManagement(eventContent, editorScrollSave) {
            this.$log.log(eventContent, editorScrollSave);
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
        submitCurrentState(redirect = false) {
            this.toggleLoading();
            this.$store.dispatch('saveData').then(
                (result) => {
                    this.toggleLoading();
                    if(redirect == true) {
                        window.location.href = result.data.redirect;
                    }
                    $('#in_survey_common').trigger('lsStopLoading');
                    window.LS.notifyFader(result.data.message, 'well-lg text-center ' + (result.data.success ? 'bg-primary' : 'bg-danger'));
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
        },
        stripScripts(s) {
            const div = document.createElement('div');
            div.innerHTML = s;
            const scripts = div.getElementsByTagName('script');
            let i = scripts.length;
            while (i--) {
                let scriptContent = scripts[i].innerHTML;
                let cleanScript = document.createElement('pre');
                cleanScript.innerHTML = `[script]
${scriptContent}
[/script]`;
                scripts[i].parentNode.appendChild(cleanScript);
                scripts[i].parentNode.removeChild(scripts[i]);
            }
            return div.innerHTML;
        },
    },
    created(){
        this.$store.dispatch('getDateFormatOptions');
        this.$store.dispatch('getDataSet'). then(
            () => {
                if(this.$store.state.permissions.editorpreset == 'source') {
                    this.descriptionSource = true;
                    this.welcomeSource = true;
                    this.endTextSource = true;
                }
                this.loading = false;
            },
            (error) => {
                this.$log.error(error);
                this.loading = false;
            }
        );

        
    },
    
    mounted() {
        $('#advancedTextEditor').on('jquery:trigger', this.jqueryTriggered);
        this.applyHotkeys();

        $('#surveytexts').find('[type="submit"]:not(.ck)').first().on('click', (e)=>{
            e.preventDefault();
            this.submitCurrentState();
        });

        $('#language').select2({
            theme: 'bootstrap'
        });
        $('#language').val(this.defaultlanguage);
        $('#language').trigger('change');

        this.toggleLoading(false);
    }
}
</script>

<template>
    <div class="container-center scoped-new-texteditor">
        <x-test id="action::surveyTexts"></x-test>
        <template v-show="!loading">
            <div class="row" v-if="languageChangerEnabled">
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
                    <input type="text" class="form-control" name="surveyls_title" id="surveyTitle" required="required" v-model="currentSurveyTitle">
                </div>
                <div class="form-group col-md-4 col-md-6" v-if="isNewSurvey">
                    <label for="createsample" class="control-label">{{'Create example question group and question?' | translate}}</label>
                    <div>
                        <input type="checkbox" name="createsample" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4 col-md-6" v-if="isNewSurvey">
                    <label class="control-label" for="language">{{'Base language' | translate }}</label>
                    <select id="language" name="language" class="form-control">
                        <option v-for="(language,key) in languageList" :value="key" :key="key">
                            {{language}}
                        </option>
                    </select>
                </div>
                <div class="form-group col-md-4 col-md-6" v-else>
                    <label class="control-label" for="language">{{'Base language' | translate }}</label>
                    <div>
                        {{languageName}}
                    </div>
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
                    <div class="ls-flex-row col-12">
                        <div class="ls-flex-item text-left">
                            <label class="">{{ "Description" | translate }}:</label>
                        </div>
                        <div class="ls-flex-item text-right" v-if="$store.state.permissions.update">
                            <button class="btn btn-default btn-xs" @click.prevent="descriptionSource=!descriptionSource"><i class="fa fa-file-code-o"></i>{{'Toggle source mode'|translate}}</button>
                        </div>
                    </div>
                    <div v-if="!$store.state.permissions.update" class="col-12" v-html="stripScripts(currentDescription)" />
                    <lsckeditor @error="CKErrorManagement" @focus="CKEventManagement" :editor="descriptionEditorObject" id="descriptionEditor" v-if="!descriptionSource && $store.state.permissions.update" v-model="currentDescription" :config="descriptionEditorConfig"></lsckeditor>
                    <aceeditor id="descriptionSource" v-if="descriptionSource && $store.state.permissions.update" v-model="currentDescription" thisId="currentDescriptionSourceEditor" :showLangSelector="false"></aceeditor>
                    <input v-if="$store.state.permissions.update" type="hidden" name="description" v-model="currentDescription" />
                </div>
            </div>
            <div class="row scoped-editor-row">
                <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                    <div class="ls-flex-row col-12">
                        <div class="ls-flex-item text-left">
                            <label class="">{{ "Welcome" | translate }}:</label>
                        </div>
                        <div class="ls-flex-item text-right" v-if="$store.state.permissions.update">
                            <button class="btn btn-default btn-xs" @click.prevent="welcomeSource=!welcomeSource"><i class="fa fa-file-code-o"></i>{{'Toggle source mode'|translate}}</button>
                        </div>
                    </div>
                    <div v-if="!$store.state.permissions.update" class="col-12" v-html="stripScripts(currentWelcome)" />
                    <lsckeditor @error="CKErrorManagement" @focus="CKEventManagement" :editor="welcomeEditorObject" id="welcomeEditor" v-if="!welcomeSource && $store.state.permissions.update" v-model="currentWelcome" :config="welcomeEditorConfig"></lsckeditor>
                    <aceeditor v-if="welcomeSource && $store.state.permissions.update" v-model="currentWelcome" thisId="currentWelcomeSourceEditor" :showLangSelector="false"></aceeditor>
                    <input v-if="$store.state.permissions.update" type="hidden" name="welcome" v-model="currentWelcome" />
                </div>
            </div>
            <div class="row scoped-editor-row">
                <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
                    <div class="ls-flex-row col-12">
                        <div class="ls-flex-item text-left">
                            <label class="">{{ "End message" | translate }}:</label>
                        </div>
                        <div class="ls-flex-item text-right" v-if="$store.state.permissions.update">
                            <button class="btn btn-default btn-xs" @click.prevent="endTextSource=!endTextSource"><i class="fa fa-file-code-o"></i>{{'Toggle source mode'|translate}}</button>
                        </div>
                    </div>
                    <div v-if="!$store.state.permissions.update" class="col-12" v-html="stripScripts(currentEndText)" />
                    <lsckeditor @error="CKErrorManagement" @focus="CKEventManagement" :editor="endTextEditorObject" id="endTextEditor" v-if="!endTextSource && $store.state.permissions.update" v-model="currentEndText" :config="endTextEditorConfig"></lsckeditor>
                    <aceeditor v-if="endTextSource && $store.state.permissions.update" v-model="currentEndText" thisId="currentEndTextSourceEditor" :showLangSelector="false"></aceeditor>
                    <input v-if="$store.state.permissions.update" type="hidden" name="endtext" v-model="currentEndText" />
                </div>
            </div>
        </template>
        <div v-if="loading"><loader-widget id="textelementsloader" /></div>
    </div>
</template>

<style lang="scss" scoped>

.scope-contains-ckeditor {
    min-height: 10rem;
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

