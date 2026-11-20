export const getQuestionById = (id, survey) => {
  // for loop with index to find the question
  let questionNumber = 0
  const questionGroups = survey?.questionGroups ?? []

  for (let i = 0; i < questionGroups.length; i++) {
    const questions = questionGroups[i].questions ?? []
    for (let j = 0; j < questions.length; j++) {
      const question = questions[j]
      ++questionNumber
      if (question.qid?.toString() === id?.toString()) {
        return { question, groupIndex: i, questionIndex: j, questionNumber }
      }
    }
  }

  return {}
}
