export default {
    setRowdata: (state, newValue) => { state.rowdata = newValue; },
    setQuestionlist: (state, newValue) => { state.questionArray = newValue; },
    setCurrentSelectedQuestion: (state, newValue) => { state.currentSelectedQuestion = newValue; },    
    setCurrentSelectedParameter: (state, newValue) => { state.currentSelectedParameter = newValue; },    
};