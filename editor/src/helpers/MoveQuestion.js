export const MoveQuestion = (questions, currentIndex, newIndex) => {
  const reorderedQuestions = insertQuestion(questions, currentIndex, newIndex)

  const movedQuestion = reorderedQuestions[newIndex]
  return { reorderedQuestions, movedQuestion }
}

const insertQuestion = (questionsList, startIndex, endIndex) => {
  const updatedList = [...questionsList]
  const [removed] = updatedList.splice(startIndex, 1)
  updatedList.splice(endIndex, 0, removed)

  return updatedList.map((question, index) => {
    question.sortOrder = index + 1
    return question
  })
}
