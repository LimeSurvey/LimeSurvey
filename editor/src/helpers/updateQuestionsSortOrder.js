export const updateQuestionsSortOrder = (questions) => {
  let i = 1
  questions.forEach((question) => {
    question.sortOrder = i
    ++i
  })

  return questions
}
