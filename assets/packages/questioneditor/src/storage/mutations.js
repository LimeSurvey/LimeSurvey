import Vue from "vue";
import isEmpty from "lodash/isEmpty";

export default {
    setCurrentQuestion : (state, newValue) => {
        state.currentQuestion = newValue;
    },
    setCurrentQuestionI10N : (state, newValue) => {
        state.currentQuestionI10N = newValue;
    },
    updateCurrentQuestionI10NValue: (state, setObject) => {
        const newCurrentQuestionI10N = state.currentQuestionI10N;
        newCurrentQuestionI10N[state.activeLanguage][setObject.value] = setObject.newValue;
        state.currentQuestionI10N = newCurrentQuestionI10N;
    },
    setQuestionTypeList : (state, newValue) => {
        state.questionTypes = newValue;
    },
    setQuestionGeneralSettings : (state, newValue) => {
        state.questionGeneralSettings = newValue;
    },
    setQuestionAdvancedSettings : (state, newValue) => {
        state.questionAdvancedSettings = newValue;
    },
    setQuestionAdvancedSettingsCategory : (state, newValue) => {
        state.questionAdvancedSettingsCategory = newValue;
    },
    setQuestionAdvancedSetting : (state, payload) => {
        console.ls.log("STORE -> ", {
            questionAdvancedSettings: state.questionAdvancedSettings,
            questionAdvancedSettingsCategory: state.questionAdvancedSettingsCategory,
            newValue: payload.newValue,
            settingName: payload.settingName
        });
        Vue.set(
            state.questionAdvancedSettings[state.questionAdvancedSettingsCategory][payload.settingName],
            'formElementValue',
            payload.newValue
        );
    },
    setActiveLanguage : (state, newValue) => {
        state.activeLanguage = newValue;
    },
    setLanguages : (state, newValue) => {
        state.languages = newValue;
    },
    setQuestionAttributes : (state, newValue) => {
        state.questionAttributes = newValue;
    },
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
    setSurvey : (state, newValue) => {
        state.survey = newValue;
    },
};