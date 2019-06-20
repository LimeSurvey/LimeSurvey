import isEmpty from 'lodash/isEmpty';
export default {
    fullyLoaded: (state) => {
        return !isEmpty(state.currentQuestionGroup);
    },
    surveyid: (state) => (LS.parameters.$GET.surveyid || LS.parameters.keyValuePairs.surveyid || null)
};