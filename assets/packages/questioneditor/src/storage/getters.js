import isEmpty from 'lodash/isEmpty';
import reduce from 'lodash/reduce';
import uniqBy from 'lodash/uniqBy';

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

    hasTitleSet: (state) => {
        return !isEmpty(state.currentQuestion.title);
    },
    hasIndividualSubquestionTitles: (state) => {
        return reduce(
            state.currentQuestionSubquestions, 
            (coll, scaleArray, scaleId) => {
                return coll && (uniqBy(scaleArray, 'title').length == scaleArray.length);
            }, 
            true
        );
    },
    hasIndividualAnsweroptionCodes: (state) => {
        return reduce(
            state.currentQuestionAnswerOptions, 
            (coll, scaleArray, scaleId) => {
                return coll && (uniqBy(scaleArray, 'code').length == scaleArray.length);
            }, 
            true
        );
    },

    canSubmit: (state, getters) => {
        return getters.hasTitleSet
        && getters.hasIndividualSubquestionTitles
        && getters.hasIndividualAnsweroptionCodes;
    }

};
