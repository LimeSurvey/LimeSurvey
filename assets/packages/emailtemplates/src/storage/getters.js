import isEmpty from 'lodash/isEmpty';
export default {
    fullyLoaded: (state) => {
        return !isEmpty(state.welcome);
    },
    surveyid: (state) => (LS.parameters.$GET.surveyid || LS.parameters.keyValuePairs.surveyid || null)
};