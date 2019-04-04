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
};