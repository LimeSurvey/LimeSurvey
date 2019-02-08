import Vue from "vue";
import isEmpty from "lodash/isEmpty";

export default {
    //mutables
    setCurrentQuestion : (state, newValue) => {
        state.currentQuestion = newValue;
    },
    setCurrentQuestionI10N : (state, newValue) => {
        state.currentQuestionI10N = newValue;
    },
    setCurrentQuestionAttributes : (state, newValue) => {
        state.currentQuestionAttributes = newValue;
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

    //Immutables
    unsetImmutableQuestionAttributes : (state, newValue) => {
        state.questionAttributesImmutable = {};
    },
    setImmutableQuestionAttributes : (state, newValue) => {
        if(isEmpty(state.questionAttributesImmutable)) {
            state.questionAttributesImmutable = newValue;
        }
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
    setSurvey : (state, newValue) => {
        state.survey = newValue;
    },
    setQuestionTypeList : (state, newValue) => {
        state.questionTypes = newValue;
    },
};