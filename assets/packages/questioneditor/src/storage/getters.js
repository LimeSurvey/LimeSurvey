import isEmpty from 'lodash/isEmpty';
import reduce from 'lodash/reduce';
import every from 'lodash/every';
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
        let gid = null;
        if(LS) {
            gid = LS.reparsedParameters().combined.gid;
        }
        return gid = gid || window.QuestionEditData.gid;
    },
    surveyObject: () => window.QuestionEditData.surveyObject,

    hasTitleSet: (state) => {
        const isNotEmpty = !isEmpty(state.currentQuestion.title);
        const onlyLettersAndNumbersAndStartingWithALetter = /^[a-z][a-z0-9]*$/i.test(state.currentQuestion.title);
        return isNotEmpty && onlyLettersAndNumbersAndStartingWithALetter;
    },
    hasIndividualSubquestionTitles: (state) => {
        return reduce(
            state.currentQuestionSubquestions, 
            (coll, scaleArray, scaleId) => {
                const unique = (uniqBy(scaleArray, 'title').length == scaleArray.length);
                const notEmpty  = reduce(scaleArray, (sum, curr) => (sum && curr.title != ''), true);
                return coll && unique && notEmpty;
            }, 
            true
        );
    },
    hasIndividualAnsweroptionCodes: (state) => {
        return reduce(
            state.currentQuestionAnswerOptions, 
            (coll, scaleArray, scaleId) => {
                if (!(scaleArray instanceof Array)) {
                    // NB: uniqBy does not work on objects.
                    // @see https://lodash.com/docs/#uniqBy
                    scaleArray = Object.values(scaleArray);
                }
                const result = uniqBy(scaleArray, 'code');
                const unique = result.length == scaleArray.length;
                const notEmpty  = reduce(scaleArray, (sum, curr) => (sum && curr.code != ''), true);
                return coll && unique && notEmpty;
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
