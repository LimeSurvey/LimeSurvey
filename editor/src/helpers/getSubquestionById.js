export const getSubquestionById = (id, question) => {
  if (question.subquestions && question.subquestions.length) {
    for (let i = 0; i < question.subquestions.length; i++) {
      const subquestion = question.subquestions[i]
      if (subquestion.qid === id) {
        return {
          subquestion,
          subquestionIndex: i,
        }
      }
    }
  }

  // Return empty object if no subquestion is found
  return {}
}
