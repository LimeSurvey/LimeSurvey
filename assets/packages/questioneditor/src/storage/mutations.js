import isEmpty from "lodash/isEmpty";
export default {
    setCurrentQuestion : (state, newValue) => {
        state.currentQuestion = newValue;
    },
    setQuestionAttributes : (state, newValue) => {
        state.questionAttributes = {};
    },
    unsetQuestionImmutable : (state) => {
        state.questionImmutable = newValue;
    },
    setQuestionImmutable : (state, newValue) => {
        if(isEmpty(state.questionImmutable)) {
            state.questionImmutable = newValue;
        }
    },
    setSurvey : (state, newValue) => {
        state.survey = newValue;
    },
};