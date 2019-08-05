import Vue from "vue";
import isEmpty from "lodash/isEmpty";
import keys from "lodash/keys";
import merge from "lodash/merge";
import indexOf from "lodash/indexOf";

export default {
    //mutables
    setCurrentQuestion : (state, newValue) => {
        state.currentQuestion = newValue;
    },
    setCurrentQuestionGroupInfo : (state, newValue) => {
        state.currentQuestionGroupInfo = newValue;
    },
    setCurrentQuestionI10N : (state, newValue) => {
        state.currentQuestionI10N = newValue;
    },
    setCurrentQuestionPermissions : (state, newValue) => {
        state.currentQuestionPermissions = newValue;
    },
    setCurrentQuestionGeneralSettings : (state, newValue) => {
        state.currentQuestionGeneralSettings = newValue;
    },
    setCurrentQuestionAdvancedSettings : (state, newValue) => {
        state.currentQuestionAdvancedSettings = newValue;
    },
    setCurrentQuestionAdvancedSettingsCategory : (state, newValue) => {
        state.currentQuestionAdvancedSettingsCategory = newValue;
    },
    setCurrentQuestionSubquestions : (state, newValue) => {
        state.currentQuestionSubquestions = newValue;
    },
    setCurrentQuestionAnswerOptions : (state, newValue) => {
        state.currentQuestionAnswerOptions = newValue;
    },

    //Update currently set values
    updateCurrentQuestion(state, valueObject) {
        state.currentQuestion = merge({}, valueObject, state.currentQuestion);
        Vue.set(state.currentQuestion, 'typeInformation', valueObject.typeInformation);
    },
    updateCurrentQuestionGeneralSettings(state, valueObject) {
        state.currentQuestionGeneralSettings = merge({}, valueObject, state.currentQuestionGeneralSettings);
    },
    updateCurrentQuestionAdvancedSettings(state, valueObject) {
        state.currentQuestionAdvancedSettings = merge({}, valueObject, state.currentQuestionAdvancedSettings);
    },
    updateCurrentQuestionSubquestions(state, valueObject) {
        state.currentQuestionSubquestions = merge({}, valueObject, state.currentQuestionSubquestions);
    },
    updateCurrentQuestionAnswerOptions(state, valueObject) {
        state.currentQuestionAnswerOptions = merge({}, valueObject, state.currentQuestionAnswerOptions);
    },

    //Immutables

    unsetQuestionImmutable : (state) => {
        state.questionImmutable = {};
    },
    setQuestionImmutable : (state, newValue) => {
        if(isEmpty(state.questionImmutable)) {
            state.questionImmutable = newValue;
        }
    },

    unsetQuestionImmutableI10N : (state) => {
        state.questionImmutableI10N = {};
    },
    setQuestionImmutableI10N : (state, newValue) => {
        if(isEmpty(state.questionImmutableI10N)) {
            state.questionImmutableI10N = newValue;
        }
    },

    unsetImmutableQuestionGeneralSettings : (state, newValue) => {
        state.questionAttributesImmutable = {};
    },
    setImmutableQuestionGeneralSettings : (state, newValue) => {
        if(isEmpty(state.questionAttributesImmutable)) {
            state.questionAttributesImmutable = newValue;
        }
    },

    unsetImmutableQuestionAdvancedSettings : (state, newValue) => {
        state.questionGeneralSettingsImmutable = {};
    },
    setImmutableQuestionAdvancedSettings : (state, newValue) => {
        if(isEmpty(state.questionGeneralSettingsImmutable)) {
            state.questionGeneralSettingsImmutable = newValue;
        }
    },

    unsetQuestionSubquestionsImmutable : (state, newValue) => {
        state.questionSubquestionsImmutable = {};
    },
    setQuestionSubquestionsImmutable : (state, newValue) => {
        if(isEmpty(state.questionSubquestionsImmutable)) {
            state.questionSubquestionsImmutable = newValue;
        }
    },
    
    unsetQuestionAnswerOptionsImmutable : (state, newValue) => {
        state.questionAnswerOptionsImmutable = {};
    },
    setQuestionAnswerOptionsImmutable : (state, newValue) => {
        if(isEmpty(state.questionAnswerOptionsImmutable)) {
            state.questionAnswerOptionsImmutable = newValue;
        }
    },

    //special and single settings
    setQuestionGeneralSetting : (state, payload) => {
        //const newCurrentQuestionGeneralSettings = state.currentQuestionGeneralSettings;
        //newCurrentQuestionGeneralSettings[payload.settingName]['formElementValue'] = payload.newValue;
        //state.currentQuestionGeneralSettings = newCurrentQuestionGeneralSettings;
        Vue.set(
            state.currentQuestionGeneralSettings[payload.settingName],
            'formElementValue',
            payload.newValue
        );
    },
    setQuestionAdvancedSetting : (state, payload) => {
        Vue.set(
            state.currentQuestionAdvancedSettings[state.questionAdvancedSettingsCategory][payload.settingName],
            'formElementValue',
            payload.newValue
        );
    },
    updateCurrentQuestionI10NValue: (state, setObject) => {
        const newCurrentQuestionI10N = state.currentQuestionI10N;
        newCurrentQuestionI10N[state.activeLanguage][setObject.value] = setObject.newValue;
        state.currentQuestionI10N = newCurrentQuestionI10N;
    },
    updateCurrentQuestionTitle: (state, newValue) => {
        Vue.set(state.currentQuestion,'title',newValue);
    },

    //view controllers
    setQuestionAdvancedSettingsCategory : (state, newValue) => {
        state.questionAdvancedSettingsCategory = newValue;
    },
    setActiveLanguage : (state, newValue) => {
        state.activeLanguage = newValue;
    },
    setLanguages : (state, newValue) => {
        state.languages = newValue;
    },
    nextLanguage: (state) => {
        let keyList = keys(state.languages);
        let currentIndex = indexOf(keyList, state.activeLanguage);
        if(currentIndex < keyList.length) {
            state.activeLanguage = keyList[currentIndex+1];
        }
    },
    previousLanguage: (state) => {
        let keyList = keys(state.languages);
        let currentIndex = indexOf(keyList, state.activeLanguage);
        if(currentIndex > 0) {
            state.activeLanguage = keyList[currentIndex-1];
        }
    },
    setSurvey : (state, newValue) => {
        state.survey = newValue;
    },
    setQuestionTypeList : (state, newValue) => {
        state.questionTypes = newValue;
    },
    toggleDebugMode: (state) => {
        state.debugMode = !state.debugMode;
    },
    setInTransfer: (state, transferState) => {
        state.inTransfer = transferState;
    },
    setStoredEvent: (state, newEvent) => {
        state.storedEvent = newEvent;
    }
};