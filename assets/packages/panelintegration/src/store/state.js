export default  function(userid) {
    return {
        rowdata: [],
        questionArray: [],
        currentSelectedQuestion: null,
        currentSelectedParameter: {
            id : Math.floor(Math.random()*100000),
            parameter : '',
            targetQuestionText : '',
            sid : '',
            qid : '',
            sqid : ''
        }
    };
};