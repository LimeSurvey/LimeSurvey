import Vue from "vue";
import pickBy from 'lodash/pickBy';
import keys from "lodash/keys";
import merge from "lodash/merge";
import indexOf from "lodash/indexOf";

export default {
    //mutables
    setCurrentQuestionGroup : (state, newValue) => {
        state.currentQuestionGroup = newValue;
    },
    setCurrentQuestionGroupI10N : (state, newValue) => {
        state.currentQuestionGroupI10N = newValue;
    },
    setQuestionList : (state, newValue) => {
        state.questionList = pickBy(newValue, (questionData,key) => {return key !== 'debug'});
    },
    setPermissions : (state, newValue) => {
        state.permissions = newValue;
    },
    //Update currently set values
    updateCurrentQuestionGroup(state, valueObject) {
        state.currentQuestionGroup = merge({}, state.currentQuestionGroup, valueObject);
    },
    setCurrentQuestionGroupI10NForCurrentLanguage : (state, payload) => {
        Vue.set(
            state.currentQuestionGroupI10N[state.activeLanguage],
            payload.setting,
            payload.newValue
        );
    },
    //special and single settings
    setCurrentQuestionGroupSetting : (state, payload) => {
        Vue.set(
            state.currentQuestionGroup,
            payload.setting,
            payload.newValue
        );
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
    setQuestionTypeList : (state, newValue) => {
        state.questionTypes = newValue;
    },
    toggleDebugMode: (state) => {
        state.debugMode = !state.debugMode;
    },
    setInTransfer: (state, transferState) => {
        state.inTransfer = transferState;
    }
};