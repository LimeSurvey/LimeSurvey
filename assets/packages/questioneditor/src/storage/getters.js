import isEmpty from 'lodash/isEmpty';
export default {
    fullyLoaded: (state) => {
        return !isEmpty(state.currentQuestion);
    },
    currentLanguageQuestionI10N: (state) => {
        let returner = {};
        try {
            returner = state.currentQuestionI10[state.activeLanguage];
        } catch(e){}
        return returner;
    },
    surveyid: () => (window.QuestionEditData.surveyObject.sid),
    gid: () => {
        if(LS) {
            return LS.reparsedParameters().combined.gid;
        }
        return window.QuestionEditData.gid;
    },
    surveyObject: () => window.QuestionEditData.surveyObject,
    currentSelectedTheme: (state) => {
        return state.currentSelectedTheme;
    },
};
