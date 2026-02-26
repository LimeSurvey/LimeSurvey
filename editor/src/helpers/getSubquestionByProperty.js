export const getSubquestionByProperty = (
  propertyValue,
  propertyName,
  question
) => {
  if (question.subquestions && question.subquestions.length) {
    for (let i = 0; i < question.subquestions.length; i++) {
      const subquestion = question.subquestions[i]

      if (subquestion[propertyName] === propertyValue) {
        return {
          subquestion,
          subquestionIndex: i,
        }
      }
    }
  }

  // Return empty object if no answer is found
  return {}
}
