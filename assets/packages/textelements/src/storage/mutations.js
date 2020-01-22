import Vue from "vue";
import keys from "lodash/keys";
import indexOf from "lodash/indexOf";

export default {
    setSurveyTitle : (state, newValue) => {
        state.surveyTitle = newValue;
    },
    setWelcome : (state, newValue) => {
        state.welcome = newValue;
    },
    setDescription : (state, newValue) => {
        state.description = newValue;
    },
    setEndText : (state, newValue) => {
        state.endText = newValue;
    },
    setEndUrl : (state, newValue) => {
        state.endUrl = newValue;
    },
    setEndUrlDescription : (state, newValue) => {
        state.endUrlDescription = newValue;
    },
    setDateFormat : (state, newValue) => {
        state.dateFormat = newValue;
    },
    setDecimalDivider : (state, newValue) => {
        state.decimalDivider = newValue;
    },
    setDateFormatOptions : (state, newValue) => {
        state.dateFormatOptions = newValue;
    },
    setPermissions : (state, newValue) => {
        state.permissions = newValue;
    },
    
    //language specific setter
    setSurveyTitleForCurrentLanguage : (state, newValue) => {
        Vue.set(state.surveyTitle, state.activeLanguage, newValue);
    },
    setWelcomeForCurrentLanguage : (state, newValue) => {
        Vue.set(state.welcome, state.activeLanguage, newValue);
    },
    setDescriptionForCurrentLanguage : (state, newValue) => {
        Vue.set(state.description, state.activeLanguage, newValue);
    },
    setEndTextForCurrentLanguage : (state, newValue) => {
        Vue.set(state.endText, state.activeLanguage, newValue);
    },
    setEndUrlForCurrentLanguage : (state, newValue) => {
        Vue.set(state.endUrl, state.activeLanguage, newValue);
    },
    setEndUrlDescriptionForCurrentLanguage : (state, newValue) => {
        Vue.set(state.endUrlDescription, state.activeLanguage, newValue);
    },
    setDateFormatForCurrentLanguage : (state, newValue) => {
        Vue.set(state.dateFormat, state.activeLanguage, newValue);
    },
    setDecimalDividerForCurrentLanguage : (state, newValue) => {
        Vue.set(state.decimalDivider, state.activeLanguage, newValue);
    },

    //view controllers
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
    toggleDebugMode: (state) => {
        state.debugMode = !state.debugMode;
    }
};