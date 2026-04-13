export const getQuestionById = (id, survey) => {
  // for loop with index to find the question
  let questionNumber = 0

  for (let i = 0; i < survey.questionGroups.length; i++) {
    const group = survey.questionGroups[i]
    for (let j = 0; j < group.questions.length; j++) {
      const question = group.questions[j]
      ++questionNumber
      if (question.qid?.toString() === id?.toString()) {
        return { question, groupIndex: i, questionIndex: j, questionNumber }
      }
    }
  }

  return {}
}
