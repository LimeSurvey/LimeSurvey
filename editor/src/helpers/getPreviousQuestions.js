const hasPreviousQuestions = (
  survey,
  group,
  question,
  currentGroupIndex,
  currentQuestionIndex
) => {
  if (!survey?.questionGroups || !group?.questions) return false

  const groupIndex = survey.questionGroups.indexOf(group)
  const questionIndex = group.questions.indexOf(question)

  if (groupIndex === -1 || questionIndex === -1) return false

  return (
    groupIndex < currentGroupIndex ||
    (groupIndex === currentGroupIndex && questionIndex < currentQuestionIndex)
  )
}

export const getPreviousQuestions = (
  survey,
  currentGroupIndex,
  currentQuestionIndex
) => {
  if (!survey || typeof survey !== 'object') {
    return []
  }

  const questions = []
  const questionGroups = survey.questionGroups || []

  questionGroups.forEach((group) => {
    const groupQuestions = group?.questions || []

    groupQuestions.forEach((question) => {
      if (!question) return

      if (
        hasPreviousQuestions(
          survey,
          group,
          question,
          currentGroupIndex,
          currentQuestionIndex
        )
      ) {
        questions.push(question)
      }
    })
  })

  return questions
}
