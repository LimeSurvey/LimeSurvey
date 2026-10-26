export const getAnswerByProperty = (propertyValue, propertyName, question) => {
  if (question.answers && question.answers.length) {
    for (let i = 0; i < question.answers.length; i++) {
      const answer = question.answers[i]

      if (answer[propertyName] === propertyValue) {
        return {
          answer,
          answerIndex: i,
        }
      }
    }
  }

  // Return empty object if no answer is found
  return {}
}
