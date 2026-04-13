export const getAnswerById = (id, question) => {
  if (question.answers && question.answers.length) {
    for (let i = 0; i < question.answers.length; i++) {
      const answer = question.answers[i]

      if (answer.aid === id) {
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
